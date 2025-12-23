# Ecommerce Conversion Audit Tool - Requirements Document

**Version:** 1.0  
**Date:** December 23, 2025  
**Type:** Internal Tool  
**Status:** Planning Phase

---

## 1. Executive Summary

### 1.1 Purpose
An internal web application for conducting comprehensive conversion optimization audits on ecommerce websites. The tool automates the discovery and analysis of common conversion blockers, performance issues, and UX problems that impact sales.

### 1.2 Goals
- Reduce manual audit time from 4-6 hours to 30 minutes per site
- Provide standardized, repeatable audit methodology
- Generate actionable reports highlighting conversion issues
- Track improvements over time with historical comparisons

### 1.3 Scope
**In Scope:**
- Automated site crawling and page discovery
- Performance analysis (Core Web Vitals, load times)
- Checkout flow testing and validation
- Mobile responsiveness evaluation
- Technical SEO checks
- Broken link/asset detection
- Screenshot capture and visual documentation

**Out of Scope (v1):**
- A/B testing functionality
- Real-time monitoring
- Multi-user collaboration features
- Client-facing dashboard
- Competitive analysis
- ML-based recommendations

---

## 2. Technical Stack

### 2.1 Backend Framework
- **Laravel 11.x** - Primary application framework
- **PHP 8.2+** - Runtime environment
- **Laravel Horizon** - Queue monitoring and management
- **Laravel Telescope** - Debugging and insight tool (dev only)

### 2.2 Database
- **PostgreSQL 15+** - Primary data store
- **Redis 7+** - Queue backend and caching layer

### 2.3 Frontend
- **Blade Templates** - Server-side rendering
- **Alpine.js 3.x** - Minimal client-side interactivity
- **Tailwind CSS** - Styling framework
- **Chart.js** - Data visualization

### 2.4 Auditing Tools
- **Spatie Laravel Crawler** - Site crawling and link validation
- **Puppeteer (Node.js)** - Headless browser automation
- **Browsershot** - Laravel wrapper for Puppeteer
- **Google Lighthouse CLI** - Performance auditing
- **Pixelmatch** - Screenshot comparison (optional)

### 2.5 Infrastructure
- **Docker** - Containerization for consistent environments
- **Hetzner Cloud** or **DigitalOcean** - VPS hosting
- **Nginx** - Web server
- **Supervisor** - Process management for queue workers

---

## 3. Functional Requirements

### 3.1 Audit Management

#### 3.1.1 Create New Audit
**Description:** Users can initiate a new audit by providing an ecommerce site URL.

**Acceptance Criteria:**
- User enters target URL via web form
- System validates URL format and accessibility
- User can optionally specify:
  - Maximum pages to crawl (default: 50)
  - Specific pages to include (homepage, product pages, checkout, etc.)
  - Authentication credentials if site requires login
  - User agent string (desktop/mobile)
- System creates audit record and queues job
- User receives confirmation with estimated completion time
- User can view audit status in real-time

#### 3.1.2 View Audit Results
**Description:** Users can view completed audit results in a structured dashboard.

**Acceptance Criteria:**
- Results organized into logical sections (Performance, Mobile, SEO, etc.)
- Each issue includes:
  - Severity level (Critical, High, Medium, Low)
  - Specific page(s) affected
  - Screenshot evidence where applicable
  - Recommended fix
  - Links to affected URLs
- Overall audit score (0-100)
- Export results as PDF report
- Share results via unique URL

#### 3.1.3 Audit History
**Description:** Users can view historical audits for a given domain.

**Acceptance Criteria:**
- List all audits for a domain, sorted by date
- Compare scores between audits
- View trend graphs showing improvement/degradation over time
- Filter audits by date range
- Delete old audits

### 3.2 Performance Analysis

#### 3.2.1 Core Web Vitals
**Description:** Measure and report on Google's Core Web Vitals metrics.

**Acceptance Criteria:**
- Capture for key pages:
  - Largest Contentful Paint (LCP)
  - First Input Delay (FID) / Interaction to Next Paint (INP)
  - Cumulative Layout Shift (CLS)
- Test both mobile and desktop
- Flag metrics that fail Google's thresholds
- Provide context on what each metric means
- Identify specific elements causing poor scores

#### 3.2.2 Page Load Performance
**Description:** Comprehensive page load analysis.

**Acceptance Criteria:**
- Total page load time
- Time to First Byte (TTFB)
- First Contentful Paint (FCP)
- DOM Content Loaded
- Full page load time
- Resource breakdown (JS, CSS, images, fonts)
- Number of requests
- Total page weight
- Blocking resources identified
- Comparison against industry benchmarks

#### 3.2.3 Lighthouse Audits
**Description:** Run full Lighthouse audits on key pages.

**Acceptance Criteria:**
- Performance score (0-100)
- Accessibility score
- Best Practices score
- SEO score
- PWA score (if applicable)
- Detailed opportunity recommendations
- Store full JSON report for historical comparison

### 3.3 Checkout Flow Testing

#### 3.3.1 Add to Cart Flow
**Description:** Test the add-to-cart functionality.

**Acceptance Criteria:**
- Navigate to product page
- Verify "Add to Cart" button is visible and clickable
- Click button and verify cart updates
- Check for success messaging
- Verify cart icon/counter updates
- Capture screenshots at each step
- Flag if flow fails or takes >3 seconds
- Test across 3-5 sample products

#### 3.3.2 Cart Page Analysis
**Description:** Evaluate the shopping cart page.

**Acceptance Criteria:**
- Verify cart displays items correctly
- Check quantity adjustment functionality
- Test remove item functionality
- Verify subtotal/total calculations
- Check for upsell/cross-sell opportunities
- Verify shipping calculator (if present)
- Check for coupon/promo code field
- Verify prominent "Proceed to Checkout" CTA
- Test cart persistence (refresh page)

#### 3.3.3 Checkout Process
**Description:** Walk through the complete checkout flow.

**Acceptance Criteria:**
- Navigate through each checkout step
- Capture screenshot of each page/step
- Verify required fields are marked
- Test form validation
- Check for guest checkout option
- Verify payment methods displayed
- Check for trust badges/security indicators
- Verify shipping options are clear
- Test back navigation between steps
- Check mobile experience specifically
- Flag any steps requiring >5 form fields
- Flag missing progress indicator

#### 3.3.4 Checkout Friction Points
**Description:** Identify common conversion blockers in checkout.

**Acceptance Criteria:**
- Detect forced account creation
- Identify unexpected costs (shipping, fees)
- Flag complex forms
- Check for exit intent popups
- Verify SSL certificate on checkout pages
- Check for multiple payment options
- Identify missing shipping calculator
- Flag slow-loading checkout pages

### 3.4 Mobile Responsiveness

#### 3.4.1 Viewport Testing
**Description:** Test site across common mobile viewport sizes.

**Acceptance Criteria:**
- Test at minimum 3 viewports:
  - Mobile: 375x667 (iPhone SE)
  - Tablet: 768x1024 (iPad)
  - Desktop: 1920x1080
- Capture full-page screenshots for each
- Flag horizontal scrolling on mobile
- Identify text too small to read (<12px)
- Check tap target sizes (minimum 48x48px)

#### 3.4.2 Mobile-Specific Issues
**Description:** Identify mobile usability problems.

**Acceptance Criteria:**
- Check for mobile-friendly navigation
- Verify hamburger menu works
- Test form inputs on mobile
- Check if modals are usable
- Verify sticky elements don't obstruct content
- Test touch interactions (swipe, tap, pinch)
- Flag Flash or unsupported technologies
- Check for app download interstitials

#### 3.4.3 Mobile Performance
**Description:** Measure mobile-specific performance metrics.

**Acceptance Criteria:**
- Run Lighthouse mobile audit
- Test on simulated 3G connection
- Measure JavaScript execution time
- Check for render-blocking resources
- Verify image optimization for mobile
- Flag large hero images on mobile

### 3.5 SEO Checks

#### 3.5.1 On-Page SEO
**Description:** Analyze technical SEO elements.

**Acceptance Criteria:**
- Verify title tags present and appropriate length (50-60 chars)
- Check meta descriptions (150-160 chars)
- Verify H1 tags (one per page)
- Check heading hierarchy (H1-H6 logical structure)
- Verify image alt attributes
- Check for canonical tags
- Verify Open Graph tags
- Check Twitter Card tags
- Flag duplicate title/meta descriptions across pages

#### 3.5.2 Product Page SEO
**Description:** Specific SEO checks for product pages.

**Acceptance Criteria:**
- Verify structured data (Schema.org Product markup)
- Check for product name in title
- Verify product description present
- Check price markup
- Verify availability markup
- Check for review/rating markup
- Verify breadcrumb markup

#### 3.5.3 Technical SEO
**Description:** Technical SEO fundamentals.

**Acceptance Criteria:**
- Verify robots.txt exists and is accessible
- Check sitemap.xml exists and is valid
- Verify HTTPS implementation
- Check for mixed content warnings
- Verify 404 page exists and is helpful
- Check redirect chains (max 1 redirect)
- Flag 5xx errors
- Verify proper use of 301 vs 302 redirects

### 3.6 Site Crawling & Link Validation

#### 3.6.1 Automated Crawling
**Description:** Discover and map site structure.

**Acceptance Criteria:**
- Start from homepage and crawl up to configured max pages
- Respect robots.txt
- Follow internal links only (ignore external)
- Capture URL, status code, and load time for each page
- Build site map visualization
- Identify orphaned pages
- Calculate crawl depth for each page

#### 3.6.2 Broken Links
**Description:** Identify all broken links and assets.

**Acceptance Criteria:**
- Check all internal links (4xx, 5xx errors)
- Check all external links
- Check all image sources
- Check CSS and JavaScript resources
- Report broken links with:
  - Source page URL
  - Broken link URL
  - Status code received
  - Link text/context
- Group by status code
- Calculate broken link percentage

#### 3.6.3 Asset Analysis
**Description:** Analyze images and media files.

**Acceptance Criteria:**
- Identify oversized images (>200KB)
- Check for missing alt attributes
- Verify image formats (flag BMP, TIFF)
- Suggest WebP conversion opportunities
- Check for lazy loading implementation
- Identify videos without transcripts

### 3.7 Reporting

#### 3.7.1 Summary Dashboard
**Description:** High-level overview of audit results.

**Acceptance Criteria:**
- Overall audit score (weighted average)
- Critical issues count (requires immediate attention)
- Issue breakdown by category
- Top 5 priority fixes
- Performance metrics summary
- Mobile vs desktop comparison
- Historical trend graph (if previous audits exist)

#### 3.7.2 Detailed Reports
**Description:** In-depth findings for each category.

**Acceptance Criteria:**
- Issues grouped by severity and category
- Each issue includes:
  - Description
  - Affected URL(s)
  - Visual evidence (screenshot)
  - Impact assessment
  - Recommended fix
  - Priority level
- Expandable/collapsible sections
- Filterable by severity, category, page
- Printable format

#### 3.7.3 PDF Export
**Description:** Generate shareable PDF report.

**Acceptance Criteria:**
- Professional formatting with branding
- Executive summary on first page
- Table of contents
- Embedded screenshots (max 2MB file size)
- Issue summaries with page references
- Recommendations section
- Generate within 30 seconds
- Download link expires after 7 days

---

## 4. Non-Functional Requirements

### 4.1 Performance

#### 4.1.1 Audit Execution Time
- Complete audit of 50 pages in ≤15 minutes
- Queue multiple audits concurrently (max 3 simultaneous)
- Provide progress updates every 10 seconds

#### 4.1.2 Application Response Time
- Page load time <2 seconds
- Audit list pagination <500ms
- Real-time status updates via polling (5-second interval)

#### 4.1.3 Resource Usage
- Single audit uses <2GB RAM
- Database size grows <100MB per audit
- Screenshot storage <50MB per audit

### 4.2 Reliability

#### 4.2.1 Error Handling
- Gracefully handle inaccessible websites (timeouts, DNS failures)
- Continue audit if individual pages fail (mark as failed, continue)
- Retry failed requests 2x before marking as error
- Log all errors with context for debugging

#### 4.2.2 Data Integrity
- Store complete audit results (prevent partial data)
- Backup database daily
- Retain audit results for 90 days minimum
- Implement database transactions for critical operations

#### 4.2.3 Uptime
- Target 99% uptime (internal tool, not mission-critical)
- Automated health checks every 5 minutes
- Email notification on service failures

### 4.3 Security

#### 4.3.1 Authentication
- Laravel Breeze for simple authentication
- Email/password login only (internal use)
- Password requirements: min 12 characters
- Session timeout after 8 hours of inactivity

#### 4.3.2 Authorization
- Single user role initially (admin)
- Future: Add read-only role for viewing results

#### 4.3.3 Data Protection
- Store credentials encrypted in database (for authenticated audits)
- HTTPS only (no HTTP access)
- Sanitize all user inputs
- No sensitive data in logs
- Rate limiting on audit creation (max 10/hour per user)

#### 4.3.4 Third-Party Site Data
- Do not store full page HTML (only excerpts for issues)
- Screenshots stored in private storage (not publicly accessible)
- Clear audit data older than 90 days

### 4.4 Scalability

#### 4.4.1 Horizontal Scaling
- Application designed to run on single VPS initially
- Queue workers can scale to multiple processes
- Database connection pooling configured
- Redis for shared state between workers

#### 4.4.2 Storage Scaling
- Screenshots stored in object storage (S3-compatible)
- Database partitioning strategy for audits table if needed
- Implement audit archival after 90 days

### 4.5 Maintainability

#### 4.5.1 Code Quality
- Follow Laravel best practices and conventions
- PSR-12 coding standards
- Unit test coverage >70% for critical paths
- Feature tests for all audit workflows
- PHPStan level 5+ static analysis

#### 4.5.2 Documentation
- README with setup instructions
- API documentation for audit jobs
- Inline code comments for complex logic
- Runbook for common operational tasks
- Architecture decision records (ADRs)

#### 4.5.3 Monitoring & Logging
- Laravel Horizon dashboard for queue monitoring
- Application logs in JSON format
- Log levels: DEBUG (dev), INFO (prod)
- Track key metrics:
  - Audit completion rate
  - Average audit duration
  - Failed audit percentage
  - Queue depth

### 4.6 Usability

#### 4.6.1 Interface Design
- Clean, minimal interface (internal tool)
- Mobile-responsive (basic support)
- Intuitive navigation
- Loading states for all async operations
- Error messages are clear and actionable

#### 4.6.2 Accessibility
- Basic WCAG 2.1 Level A compliance
- Keyboard navigation support
- Sufficient color contrast
- Form labels and ARIA attributes

---

## 5. System Architecture

### 5.1 High-Level Architecture

```
┌─────────────┐
│   Browser   │
└──────┬──────┘
       │ HTTPS
       ▼
┌─────────────────────────────────────────┐
│         Nginx (Web Server)              │
└──────┬──────────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────────┐
│      Laravel Application                │
│  ┌─────────────────────────────────┐   │
│  │  Web Routes (Blade Views)       │   │
│  └─────────────────────────────────┘   │
│  ┌─────────────────────────────────┐   │
│  │  Audit Service Layer            │   │
│  └─────────────────────────────────┘   │
│  ┌─────────────────────────────────┐   │
│  │  Queue Jobs                     │   │
│  │  - CrawlSiteJob                 │   │
│  │  - AnalyzePerformanceJob        │   │
│  │  - TestCheckoutJob              │   │
│  │  - ValidateLinksJob             │   │
│  └─────────────────────────────────┘   │
└──────┬────────────────────┬─────────────┘
       │                    │
       ▼                    ▼
┌─────────────┐      ┌─────────────┐
│  PostgreSQL │      │    Redis    │
│  (Audits,   │      │  (Queue,    │
│   Results)  │      │   Cache)    │
└─────────────┘      └─────────────┘

External Tools (via Shell Exec):
┌─────────────────────────────────────────┐
│  - Puppeteer (Node.js)                  │
│  - Lighthouse CLI                       │
│  - Spatie Crawler (PHP)                 │
└─────────────────────────────────────────┘
```

### 5.2 Audit Workflow

```
1. User submits audit form
   ↓
2. Create Audit record (status: pending)
   ↓
3. Dispatch CrawlSiteJob to queue
   ↓
4. CrawlSiteJob discovers pages, dispatches child jobs:
   - AnalyzePerformanceJob (per page)
   - TestCheckoutJob (checkout flow)
   - ValidateLinksJob (per page)
   - CaptureScreenshotsJob (per page)
   ↓
5. Child jobs execute in parallel, store results
   ↓
6. AggregateResultsJob combines findings
   ↓
7. Update Audit record (status: completed)
   ↓
8. User views results dashboard
```

### 5.3 Queue Architecture

**Queue Configuration:**
- Default queue: General tasks (crawling, analysis)
- High priority queue: Screenshot capture, user-initiated reports
- Low priority queue: Historical comparison, cleanup tasks

**Worker Configuration:**
- 3 workers for default queue
- 2 workers for high priority queue
- 1 worker for low priority queue
- Max retry attempts: 3
- Timeout: 300 seconds per job

---

## 6. Data Models

### 6.1 Core Models

#### 6.1.1 Audit
```
audits
├── id (uuid, primary key)
├── domain (string, indexed)
├── url (text)
├── status (enum: pending, crawling, analyzing, completed, failed)
├── score (integer, 0-100, nullable)
├── pages_crawled (integer, default: 0)
├── max_pages (integer, default: 50)
├── config (json, nullable) // crawl depth, auth creds, etc.
├── started_at (timestamp, nullable)
├── completed_at (timestamp, nullable)
├── created_by (foreign key: users.id)
├── created_at (timestamp)
├── updated_at (timestamp)
```

#### 6.1.2 Page
```
pages
├── id (uuid, primary key)
├── audit_id (foreign key: audits.id, cascade on delete)
├── url (text)
├── status_code (integer)
├── title (string, nullable)
├── meta_description (text, nullable)
├── h1 (string, nullable)
├── load_time (integer, milliseconds, nullable)
├── screenshot_path (string, nullable)
├── html_excerpt (text, nullable) // First 2000 chars for context
├── crawled_at (timestamp)
├── created_at (timestamp)
├── updated_at (timestamp)
└── INDEX (audit_id, url)
```

#### 6.1.3 Issue
```
issues
├── id (uuid, primary key)
├── audit_id (foreign key: audits.id, cascade on delete)
├── page_id (foreign key: pages.id, nullable, cascade on delete)
├── category (enum: performance, mobile, seo, checkout, links, accessibility)
├── severity (enum: critical, high, medium, low, info)
├── title (string)
├── description (text)
├── recommendation (text)
├── affected_element (string, nullable) // CSS selector or description
├── screenshot_path (string, nullable)
├── metadata (json, nullable) // Additional context
├── created_at (timestamp)
├── updated_at (timestamp)
└── INDEX (audit_id, category, severity)
```

#### 6.1.4 PerformanceMetric
```
performance_metrics
├── id (uuid, primary key)
├── page_id (foreign key: pages.id, cascade on delete)
├── device_type (enum: mobile, desktop)
├── lcp (float, nullable) // Largest Contentful Paint (seconds)
├── fid (float, nullable) // First Input Delay (milliseconds)
├── cls (float, nullable) // Cumulative Layout Shift (score)
├── fcp (float, nullable) // First Contentful Paint (seconds)
├── ttfb (integer, nullable) // Time to First Byte (ms)
├── speed_index (float, nullable)
├── total_blocking_time (integer, nullable) // ms
├── lighthouse_performance_score (integer, nullable) // 0-100
├── lighthouse_accessibility_score (integer, nullable)
├── lighthouse_seo_score (integer, nullable)
├── lighthouse_best_practices_score (integer, nullable)
├── lighthouse_json (json, nullable) // Full Lighthouse report
├── created_at (timestamp)
└── INDEX (page_id, device_type)
```

#### 6.1.5 Link
```
links
├── id (uuid, primary key)
├── audit_id (foreign key: audits.id, cascade on delete)
├── source_page_id (foreign key: pages.id, cascade on delete)
├── destination_url (text)
├── link_text (string, nullable)
├── link_type (enum: internal, external, asset)
├── status_code (integer, nullable)
├── is_broken (boolean, default: false)
├── checked_at (timestamp)
└── INDEX (audit_id, is_broken)
```

#### 6.1.6 CheckoutStep
```
checkout_steps
├── id (uuid, primary key)
├── audit_id (foreign key: audits.id, cascade on delete)
├── step_number (integer)
├── step_name (string) // e.g., "Cart", "Shipping Info", "Payment"
├── url (text)
├── screenshot_path (string, nullable)
├── form_fields_count (integer, nullable)
├── errors_found (json, nullable) // Array of error descriptions
├── load_time (integer, nullable) // milliseconds
├── successful (boolean, default: true)
├── created_at (timestamp)
└── INDEX (audit_id, step_number)
```

### 6.2 Relationships

- Audit `hasMany` Pages
- Audit `hasMany` Issues
- Audit `hasMany` Links
- Audit `hasMany` CheckoutSteps
- Page `hasMany` Issues
- Page `hasOne` PerformanceMetric (per device type)
- Page `hasMany` Links (as source)

### 6.3 Database Indexes

**Critical Indexes:**
- `audits.domain` - For filtering audits by domain
- `audits.created_by` - For user's audit history
- `pages.audit_id` - For fetching audit pages
- `issues.audit_id, issues.severity` - For issue filtering
- `links.audit_id, links.is_broken` - For broken link reports

---

## 7. API Endpoints (Internal Use)

### 7.1 Audit Management

```
GET    /audits              - List all audits (paginated)
GET    /audits/create       - Show audit creation form
POST   /audits              - Create new audit
GET    /audits/{id}         - View audit details
GET    /audits/{id}/status  - Get audit status (AJAX)
DELETE /audits/{id}         - Delete audit
```

### 7.2 Results & Reporting

```
GET    /audits/{id}/results               - View full results dashboard
GET    /audits/{id}/issues                - List issues (filterable)
GET    /audits/{id}/performance           - Performance summary
GET    /audits/{id}/checkout              - Checkout flow results
GET    /audits/{id}/links                 - Broken links report
GET    /audits/{id}/export                - Generate PDF report
GET    /audits/{id}/compare/{otherId}     - Compare two audits
```

### 7.3 Historical Data

```
GET    /domains/{domain}/history          - Audit history for domain
GET    /domains/{domain}/trends           - Score trends over time
```

---

## 8. Job Queue Design

### 8.1 Job Hierarchy

```
CrawlSiteJob (main orchestrator)
├── Crawls homepage
├── Discovers internal links
├── For each page (up to max_pages):
│   ├── AnalyzePageJob
│   │   ├── Capture screenshot
│   │   ├── Extract metadata (title, meta, headings)
│   │   ├── Run SEO checks
│   │   └── Store Page record
│   │
│   ├── PerformanceAnalysisJob
│   │   ├── Run Lighthouse audit (mobile & desktop)
│   │   ├── Capture Core Web Vitals
│   │   └── Store PerformanceMetric records
│   │
│   └── ValidateLinksJob
│       ├── Extract all links from page
│       ├── Check each link's status
│       └── Store Link records
│
├── TestCheckoutFlowJob (runs once)
│   ├── Navigate to product page
│   ├── Add to cart
│   ├── View cart
│   ├── Proceed to checkout
│   ├── Fill shipping info (test data)
│   ├── View payment page
│   ├── Capture screenshots at each step
│   └── Store CheckoutStep records
│
└── AggregateResultsJob (runs after all jobs complete)
    ├── Calculate overall score
    ├── Identify critical issues
    ├── Generate issue summaries
    └── Mark audit as completed
```

### 8.2 Job Priorities

**High Priority:**
- AggregateResultsJob (user waiting for results)
- AnalyzePageJob for homepage
- TestCheckoutFlowJob

**Normal Priority:**
- AnalyzePageJob for other pages
- PerformanceAnalysisJob
- ValidateLinksJob

**Low Priority:**
- Historical comparison jobs
- Cleanup jobs (delete old screenshots)

### 8.3 Job Failure Handling

**Retry Logic:**
- Network timeouts: Retry 3x with exponential backoff
- Rate limiting: Retry after 60 seconds
- Invalid URL: Mark as failed, continue audit
- Puppeteer crashes: Restart browser instance, retry once

**Partial Failures:**
- If <30% of pages fail: Mark audit as completed with warnings
- If >30% of pages fail: Mark audit as failed
- Always store partial results for debugging

---

## 9. Configuration

### 9.1 Audit Configuration

**Default Settings:**
```php
'audit' => [
    'max_pages' => 50,
    'timeout' => 30000, // 30 seconds per page
    'max_crawl_depth' => 3,
    'user_agent' => 'AuditBot/1.0',
    'screenshot_quality' => 80,
    'screenshot_max_height' => 2000,
    'concurrent_jobs' => 3,
    'respect_robots_txt' => true,
]
```

**Puppeteer Settings:**
```php
'puppeteer' => [
    'timeout' => 30000,
    'viewport' => [
        'desktop' => ['width' => 1920, 'height' => 1080],
        'mobile' => ['width' => 375, 'height' => 667],
        'tablet' => ['width' => 768, 'height' => 1024],
    ],
    'user_agent' => [
        'desktop' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)...',
        'mobile' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)...',
    ],
]
```

**Lighthouse Settings:**
```php
'lighthouse' => [
    'categories' => ['performance', 'accessibility', 'best-practices', 'seo'],
    'throttling' => [
        'cpuSlowdownMultiplier' => 4,
        'requestLatency' => 150, // ms
        'downloadThroughput' => 1.6 * 1024 * 1024 / 8, // 1.6 Mbps
        'uploadThroughput' => 750 * 1024 / 8, // 750 Kbps
    ],
]
```

### 9.2 Queue Configuration

```php
'queue' => [
    'default' => 'redis',
    'connections' => [
        'redis' => [
            'driver' => 'redis',
            'queue' => 'default',
            'retry_after' => 300, // 5 minutes
            'block_for' => null,
        ],
    ],
    'failed' => [
        'database' => 'pgsql',
        'table' => 'failed_jobs',
    ],
]
```

---

## 10. Deployment

### 10.1 Server Requirements

**Minimum Specifications:**
- 2 CPU cores
- 4GB RAM
- 50GB SSD storage
- Ubuntu 22.04 LTS or later

**Recommended for Production:**
- 4 CPU cores
- 8GB RAM
- 100GB SSD storage
- Automated backups

### 10.2 Software Requirements

**System Packages:**
- PHP 8.2+ with extensions: pdo, pdo_pgsql, mbstring, xml, curl, zip, gd
- PostgreSQL 15+
- Redis 7+
- Nginx 1.18+
- Node.js 18+ (for Puppeteer and Lighthouse)
- Supervisor (for queue workers)
- Certbot (for SSL certificates)

**Node.js Global Packages:**
- Puppeteer
- Lighthouse

### 10.3 Docker Deployment

**Docker Compose Services:**
```yaml
services:
  app:
    - Laravel application
    - PHP 8.2-fpm
    - All PHP extensions
    
  nginx:
    - Web server
    - SSL termination
    
  postgres:
    - PostgreSQL 15
    - Persistent volume for data
    
  redis:
    - Redis 7
    - Cache and queue storage
    
  worker:
    - Queue workers (3 instances)
    - Same image as app
    
  scheduler:
    - Laravel scheduler (cron)
```

### 10.4 Environment Variables

**Required:**
```
APP_NAME="Ecommerce Audit Tool"
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://audits.yourdomain.com

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=audit_tool
DB_USERNAME=audit_user
DB_PASSWORD=...

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

QUEUE_CONNECTION=redis

FILESYSTEM_DISK=local

LIGHTHOUSE_PATH=/usr/local/bin/lighthouse
PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium-browser
```

### 10.5 Deployment Process

**Initial Deployment:**
1. Clone repository to server
2. Copy `.env.example` to `.env` and configure
3. Run `composer install --optimize-autoloader --no-dev`
4. Run `npm install && npm run build`
5. Run `php artisan key:generate`
6. Run `php artisan migrate --force`
7. Run `php artisan storage:link`
8. Configure Nginx virtual host
9. Obtain SSL certificate with Certbot
10. Start queue workers with Supervisor
11. Configure Laravel scheduler cron job

**Update Deployment:**
1. Pull latest code
2. Run `composer install --optimize-autoloader --no-dev`
3. Run `npm install && npm run build`
4. Run `php artisan migrate --force`
5. Run `php artisan config:cache`
6. Run `php artisan route:cache`
7. Run `php artisan view:cache`
8. Restart queue workers
9. Reload PHP-FPM

---

## 11. Testing Strategy

### 11.1 Unit Tests

**Coverage Areas:**
- Audit scoring calculation
- Issue severity classification
- URL validation and normalization
- Screenshot path generation
- Report aggregation logic

**Target:** 70%+ code coverage for business logic

### 11.2 Feature Tests

**Critical Workflows:**
- Create audit and verify database records
- Queue job dispatch and execution
- Audit completion and status updates
- Results display with various issue types
- PDF report generation
- Historical comparison

### 11.3 Browser Tests (Dusk)

**User Flows:**
- Complete audit creation flow
- View results dashboard
- Filter issues by category/severity
- Export PDF report
- Compare two audits

### 11.4 Integration Tests

**External Tool Testing:**
- Puppeteer screenshot capture
- Lighthouse CLI execution and parsing
- Spatie Crawler functionality
- PDF generation with DomPDF/Snappy

### 11.5 Manual Testing Checklist

Before each release:
- [ ] Test audit on 3 different ecommerce sites (Shopify, WooCommerce, custom)
- [ ] Verify checkout flow detection on authenticated site
- [ ] Test mobile vs desktop Lighthouse scores
- [ ] Verify broken link detection with known broken site
- [ ] Test PDF export with various result sizes
- [ ] Verify queue workers handle failures gracefully
- [ ] Test audit deletion and cleanup
- [ ] Verify historical comparison accuracy

---

## 12. Monitoring & Maintenance

### 12.1 Application Monitoring

**Key Metrics:**
- Audit completion rate (target: >95%)
- Average audit duration (target: <15 minutes)
- Failed job rate (target: <5%)
- Queue depth (alert if >50 jobs)
- Database size growth
- Disk usage for screenshots

**Tools:**
- Laravel Horizon dashboard (queue monitoring)
- Laravel Telescope (debugging, dev only)
- Application logs via `tail -f storage/logs/laravel.log`

### 12.2 Infrastructure Monitoring

**System Metrics:**
- CPU usage (alert if >80% for 5+ minutes)
- Memory usage (alert if >90%)
- Disk space (alert if <10GB free)
- PostgreSQL connection count
- Redis memory usage

**Tools:**
- Built-in monitoring from hosting provider (Hetzner/DigitalOcean)
- Custom health check endpoint: `/health`

### 12.3 Alerting

**Critical Alerts (immediate action):**
- Application down (HTTP 500+ errors)
- Database connection failures
- All queue workers stopped
- Disk space critically low

**Warning Alerts (review within 24 hours):**
- Failed job rate >10%
- Audit duration >30 minutes
- Queue depth >100 jobs

**Notification Channels:**
- Email to ops/dev team
- Optional: Slack webhook for critical alerts

### 12.4 Maintenance Tasks

**Daily:**
- Review failed jobs in Horizon
- Check disk space usage

**Weekly:**
- Review audit success rates
- Check for PHP/composer security updates
- Review error logs for patterns

**Monthly:**
- Update dependencies (composer update)
- Review and archive old audits (>90 days)
- Database vacuum/analyze (PostgreSQL)
- Review and optimize slow queries

**Quarterly:**
- Review and update Lighthouse/Puppeteer versions
- Security audit of dependencies
- Load testing with concurrent audits
- Review and update documentation

---

## 13. Future Enhancements (Post-MVP)

### 13.1 Phase 2 Features

**Enhanced Auditing:**
- Custom audit templates (focus on specific issues)
- A/B test recommendations based on findings
- Competitor comparison audits
- International SEO checks (hreflang, etc.)
- Accessibility testing beyond basics (WCAG 2.1 AA/AAA)

**User Experience:**
- Multi-user support with roles (admin, viewer)
- Scheduled recurring audits
- Email notifications on completion
- Customizable severity thresholds
- White-label reports for client sharing

**Analysis:**
- ML-based priority recommendations
- Historical trend analysis across all audits
- Industry benchmark comparisons
- ROI calculator for fixes

### 13.2 Phase 3 Features

**Advanced Functionality:**
- Real-time monitoring (uptime, performance)
- Form submission testing (contact forms, newsletter signups)
- Cart abandonment flow simulation
- Payment gateway testing (sandbox mode)
- API for programmatic access

**Integration:**
- Google Analytics integration (correlate issues with traffic drops)
- Google Search Console integration (index coverage, search appearance)
- Slack notifications
- Webhook support for CI/CD integration

### 13.3 Technical Improvements

**Performance:**
- Distributed crawling with multiple workers
- Cached Lighthouse audits (reuse for unchanged pages)
- Incremental audits (only test changed pages)

**Scalability:**
- Kubernetes deployment for large-scale use
- Multi-region deployment
- CDN for screenshot delivery

---

## 14. Success Metrics

### 14.1 MVP Success Criteria

**Functionality:**
- Successfully audit 10 different ecommerce sites without errors
- Identify at least 15 distinct issue types
- Generate complete PDF reports in <30 seconds
- Complete 50-page audit in <15 minutes

**Quality:**
- <5% false positive rate on critical issues
- >90% user satisfaction with audit thoroughness
- Zero data loss or corruption incidents

**Performance:**
- 99% uptime over first 90 days
- <2 second page load times
- <5% failed job rate

### 14.2 Key Performance Indicators (KPIs)

**Usage:**
- Number of audits completed per week
- Average pages per audit
- Most common issue types identified

**Efficiency:**
- Time saved vs manual audits (target: 70% reduction)
- Issues identified per audit (target: average 25+)

**Quality:**
- Re-audit score improvement (target: 15+ points after fixes)
- Critical issues resolved rate

---

## 15. Risks & Mitigations

### 15.1 Technical Risks

**Risk:** Puppeteer/Lighthouse instability or crashes
**Mitigation:** Robust error handling, automatic retries, fallback to simpler checks if tools fail

**Risk:** Target sites block or rate-limit audits
**Mitigation:** Configurable delays between requests, user agent rotation, respect robots.txt

**Risk:** Storage growth from screenshots
**Mitigation:** Image compression, automatic cleanup of old audits, S3 storage for scalability

**Risk:** Long-running audits timeout
**Mitigation:** Split into smaller jobs, configurable max pages, progress tracking

### 15.2 Operational Risks

**Risk:** Single server failure causes downtime
**Mitigation:** Daily backups, documented recovery process, consider HA setup in future

**Risk:** Outdated audit methodology
**Mitigation:** Quarterly review of best practices, update Lighthouse/Puppeteer regularly

**Risk:** Data privacy concerns with client sites
**Mitigation:** Minimal data storage, encrypted credentials, clear data retention policy

---

## 16. Glossary

**Core Web Vitals:** Google's metrics for page experience (LCP, FID/INP, CLS)

**Conversion Rate Optimization (CRO):** Process of increasing the percentage of website visitors who complete a desired action

**Headless Browser:** Browser without a GUI, controlled programmatically (e.g., Puppeteer)

**Job Queue:** System for managing background tasks asynchronously

**Lighthouse:** Automated tool for improving web page quality (performance, accessibility, SEO)

**Puppeteer:** Node.js library for controlling Chrome/Chromium browser

**Time to First Byte (TTFB):** Time from request to first byte of response

**Progressive Web App (PWA):** Web app with native app-like features

---

## 17. Appendices

### Appendix A: Sample Audit Score Calculation

```
Overall Audit Score (0-100):
= (Performance * 0.30) 
  + (Mobile * 0.25) 
  + (SEO * 0.20) 
  + (Checkout * 0.15) 
  + (Links * 0.10)

Performance Score:
= Average Lighthouse performance score across pages

Mobile Score:
= (Mobile Lighthouse score * 0.60) + (Responsive issues penalty * 0.40)

SEO Score:
= (On-page SEO elements * 0.50) + (Technical SEO * 0.50)

Checkout Score:
= 100 - (Critical issues * 20) - (High issues * 10) - (Medium issues * 5)

Links Score:
= 100 - (Broken links percentage * 100)
```

### Appendix B: Issue Severity Guidelines

**Critical:** Blocks conversion/purchases
- Checkout page not loading
- Payment form broken
- Add to cart not working
- Site not loading on mobile

**High:** Significantly impacts conversion
- Slow checkout pages (>5s load time)
- Missing SSL on checkout
- Poor mobile usability
- Prominent broken links

**Medium:** Moderate impact on user experience
- Missing alt tags on product images
- Slow page load (3-5s)
- Missing meta descriptions
- Non-critical broken links

**Low:** Minor issues, nice-to-have fixes
- Suboptimal heading structure
- Missing Open Graph tags
- Small images that could be optimized
- Informational SEO improvements

### Appendix C: Recommended Hosting Providers

**Hetzner Cloud:**
- CX21: €5.83/month (2 vCPU, 4GB RAM) - Minimum
- CX31: €10.29/month (2 vCPU, 8GB RAM) - Recommended

**DigitalOcean:**
- Basic Droplet: $12/month (2 vCPU, 4GB RAM) - Minimum
- Basic Droplet: $24/month (4 vCPU, 8GB RAM) - Recommended

**Laravel Forge Compatible:**
- Both providers work with Laravel Forge for easier deployment
- Forge: $12/month for unlimited servers

---

## Document History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 2025-12-23 | Claude | Initial requirements document |

---

**Approval:**

| Role | Name | Signature | Date |
|------|------|-----------|------|
| Product Owner | [Your Name] | | |
| Lead Developer | [Your Name] | | |

---

**End of Requirements Document**
