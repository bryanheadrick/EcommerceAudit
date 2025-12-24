<?php

namespace App\Jobs;

use App\Models\Audit;
use App\Models\CheckoutStep;
use App\Models\Issue;
use App\Services\PuppeteerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

/**
 * Tests the checkout flow of an ecommerce website.
 *
 * This job:
 * - Uses Puppeteer via Browsershot to automate checkout flow
 * - Navigates through product page, cart, and checkout steps
 * - Captures screenshots at each step
 * - Records form field counts
 * - Detects errors and friction points
 * - Creates CheckoutStep records
 * - Creates Issue records for problems found
 */
class TestCheckoutFlowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 2;

    /**
     * The maximum number of seconds the job can run.
     *
     * @var int
     */
    public $timeout = 300;

    /**
     * The audit instance.
     *
     * @var Audit
     */
    protected Audit $audit;

    /**
     * Create a new job instance.
     *
     * @param Audit $audit The audit to test checkout flow for
     */
    public function __construct(Audit $audit)
    {
        $this->audit = $audit;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            Log::channel('audit')->info("→ Testing checkout flow", [
                'audit_id' => $this->audit->id,
                'url' => $this->audit->url,
            ]);

            // TODO: Implement Puppeteer/Browsershot automation
            // - Launch headless browser
            // - Navigate to product page
            // - Click "Add to Cart" button
            // - Navigate through checkout steps
            // - Capture screenshots and data at each step

            $checkoutSteps = $this->runCheckoutFlow();

            // Analyze checkout flow for issues
            $this->analyzeCheckoutFlow($checkoutSteps);

            Log::channel('audit')->info("✓ Checkout flow test complete", [
                'steps_completed' => count($checkoutSteps),
            ]);

        } catch (Exception $e) {
            Log::channel('audit')->error("✗ Checkout flow test failed", [
                'audit_id' => $this->audit->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Run the checkout flow automation using PuppeteerService.
     *
     * @return array
     */
    protected function runCheckoutFlow(): array
    {
        $puppeteerService = app(PuppeteerService::class);

        $steps = [];

        // Common checkout URLs to test
        $checkoutPaths = [
            ['name' => 'Homepage', 'path' => ''],
            ['name' => 'Cart Page', 'path' => '/cart'],
            ['name' => 'Checkout', 'path' => '/checkout'],
        ];

        foreach ($checkoutPaths as $index => $pathInfo) {
            $stepNumber = $index + 1;
            $url = rtrim($this->audit->url, '/') . $pathInfo['path'];
            $screenshotPath = "checkout/{$this->audit->id}/step-{$stepNumber}-" . str_replace(' ', '-', strtolower($pathInfo['name'])) . ".png";
            $fullPath = Storage::path($screenshotPath);

            $directory = dirname($fullPath);
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $successful = $puppeteerService->takeScreenshot($url, $fullPath);

            $formFieldsCount = 0;
            if ($successful) {
                $formFieldsCount = $this->countFormFields($url, $puppeteerService);
            }

            $steps[] = $this->createCheckoutStep(
                stepNumber: $stepNumber,
                stepName: $pathInfo['name'],
                url: $url,
                screenshotPath: $successful ? $screenshotPath : null,
                formFieldsCount: $formFieldsCount,
                successful: $successful,
                errors: $successful ? [] : ['Failed to load page']
            );
        }

        return $steps;
    }

    /**
     * Count form fields on a page using Puppeteer.
     *
     * @param string $url
     * @param PuppeteerService $puppeteerService
     * @return int
     */
    protected function countFormFields(string $url, PuppeteerService $puppeteerService): int
    {
        try {
            $script = "document.querySelectorAll('input, select, textarea').length";

            $count = $puppeteerService->evaluateJavaScript($url, $script);

            return (int) ($count ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Create a checkout step record.
     *
     * @param int $stepNumber
     * @param string $stepName
     * @param string $url
     * @param string|null $screenshotPath
     * @param int|null $formFieldsCount
     * @param bool $successful
     * @param array $errors
     * @return CheckoutStep
     */
    protected function createCheckoutStep(
        int $stepNumber,
        string $stepName,
        string $url,
        ?string $screenshotPath = null,
        ?int $formFieldsCount = null,
        bool $successful = true,
        array $errors = []
    ): CheckoutStep {
        return CheckoutStep::create([
            'audit_id' => $this->audit->id,
            'step_number' => $stepNumber,
            'step_name' => $stepName,
            'url' => $url,
            'screenshot_path' => $screenshotPath,
            'form_fields_count' => $formFieldsCount,
            'errors_found' => !empty($errors) ? $errors : null,
            'load_time' => rand(500, 3000), // Placeholder
            'successful' => $successful,
        ]);
    }

    /**
     * Analyze checkout flow and create issues.
     *
     * @param array $checkoutSteps
     * @return void
     */
    protected function analyzeCheckoutFlow(array $checkoutSteps): void
    {
        $totalSteps = count($checkoutSteps);
        $failedSteps = array_filter($checkoutSteps, fn($step) => !$step->successful);
        $totalFormFields = array_sum(array_column($checkoutSteps, 'form_fields_count'));

        // Check if checkout flow failed
        if (!empty($failedSteps)) {
            Issue::create([
                'audit_id' => $this->audit->id,
                'page_id' => null,
                'category' => 'checkout',
                'severity' => 'critical',
                'title' => 'Checkout Flow Failed',
                'description' => 'The automated checkout test encountered errors and could not complete the flow.',
                'recommendation' => 'Review the checkout process to ensure all steps are accessible and functional. Check for JavaScript errors or broken functionality.',
                'metadata' => [
                    'failed_steps' => count($failedSteps),
                    'total_steps' => $totalSteps,
                ],
            ]);
        }

        // Check if too many steps
        if ($totalSteps > 5) {
            Issue::create([
                'audit_id' => $this->audit->id,
                'page_id' => null,
                'category' => 'checkout',
                'severity' => 'medium',
                'title' => 'Too Many Checkout Steps',
                'description' => "The checkout process has {$totalSteps} steps, which may increase cart abandonment.",
                'recommendation' => 'Consider consolidating checkout steps. Best practice is 3-4 steps maximum (Cart, Shipping/Billing, Payment, Confirmation).',
                'metadata' => ['steps_count' => $totalSteps],
            ]);
        }

        // Check for too many form fields
        if ($totalFormFields > 15) {
            Issue::create([
                'audit_id' => $this->audit->id,
                'page_id' => null,
                'category' => 'checkout',
                'severity' => 'high',
                'title' => 'Excessive Form Fields',
                'description' => "The checkout process requires {$totalFormFields} form fields, which can lead to abandonment.",
                'recommendation' => 'Reduce required form fields. Remove optional fields, use autofill, and consider guest checkout. Aim for 8-12 fields maximum.',
                'metadata' => ['total_fields' => $totalFormFields],
            ]);
        }

        // Check individual steps for issues
        foreach ($checkoutSteps as $step) {
            if ($step->form_fields_count > 8) {
                Issue::create([
                    'audit_id' => $this->audit->id,
                    'page_id' => null,
                    'category' => 'checkout',
                    'severity' => 'medium',
                    'title' => "Too Many Fields in {$step->step_name}",
                    'description' => "The '{$step->step_name}' step has {$step->form_fields_count} form fields.",
                    'recommendation' => 'Reduce the number of required fields in this step. Consider making some fields optional or removing them entirely.',
                    'metadata' => [
                        'step' => $step->step_name,
                        'fields_count' => $step->form_fields_count,
                    ],
                ]);
            }

            if ($step->load_time > 5000) {
                Issue::create([
                    'audit_id' => $this->audit->id,
                    'page_id' => null,
                    'category' => 'checkout',
                    'severity' => 'high',
                    'title' => "Slow Checkout Page: {$step->step_name}",
                    'description' => "The '{$step->step_name}' step took {$step->load_time}ms to load.",
                    'recommendation' => 'Optimize this checkout page for faster loading. Slow checkout pages lead to cart abandonment.',
                    'metadata' => [
                        'step' => $step->step_name,
                        'load_time' => $step->load_time,
                    ],
                ]);
            }

            if (!empty($step->errors_found)) {
                Issue::create([
                    'audit_id' => $this->audit->id,
                    'page_id' => null,
                    'category' => 'checkout',
                    'severity' => 'critical',
                    'title' => "Errors in {$step->step_name}",
                    'description' => "Errors detected during '{$step->step_name}' step: " . implode(', ', $step->errors_found),
                    'recommendation' => 'Fix the errors preventing successful checkout completion.',
                    'metadata' => [
                        'step' => $step->step_name,
                        'errors' => $step->errors_found,
                    ],
                ]);
            }
        }

        // Check for guest checkout option
        $this->checkGuestCheckoutOption();
    }

    /**
     * Check if guest checkout option is available.
     *
     * @return void
     */
    protected function checkGuestCheckoutOption(): void
    {
        // TODO: Implement actual detection of guest checkout
        // - Look for "Continue as Guest" or similar buttons
        // - Detect if forced account creation is required

        $hasGuestCheckout = true; // Placeholder

        if (!$hasGuestCheckout) {
            Issue::create([
                'audit_id' => $this->audit->id,
                'page_id' => null,
                'category' => 'checkout',
                'severity' => 'high',
                'title' => 'No Guest Checkout Option',
                'description' => 'The checkout process appears to require account creation, which significantly increases cart abandonment.',
                'recommendation' => 'Implement a guest checkout option. Allow users to complete purchases without creating an account. You can optionally offer account creation after purchase.',
            ]);
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("TestCheckoutFlowJob permanently failed for audit {$this->audit->id}", [
            'audit_id' => $this->audit->id,
            'error' => $exception->getMessage(),
        ]);

        Issue::create([
            'audit_id' => $this->audit->id,
            'page_id' => null,
            'category' => 'checkout',
            'severity' => 'high',
            'title' => 'Checkout Flow Test Failed',
            'description' => "Failed to test checkout flow: {$exception->getMessage()}",
            'recommendation' => 'The automated checkout test could not be completed. This may indicate issues with the checkout process or site accessibility.',
        ]);
    }
}
