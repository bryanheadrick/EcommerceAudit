# Ecommerce Audit Tool

An internal web application for conducting comprehensive conversion optimization audits on ecommerce websites. Built with Laravel 11, this tool automates the discovery and analysis of common conversion blockers, performance issues, and UX problems that impact sales.

## 🎯 Project Goals

- Reduce manual audit time from 4-6 hours to 30 minutes per site
- Provide standardized, repeatable audit methodology
- Generate actionable reports highlighting conversion issues
- Track improvements over time with historical comparisons

## 🏗️ Tech Stack

### Backend
- **Laravel 11.x** - Primary application framework
- **PHP 8.2+** - Runtime environment
- **PostgreSQL 15+** - Primary data store
- **Redis 7+** - Queue backend and caching layer
- **Laravel Horizon** - Queue monitoring and management
- **Laravel Telescope** - Debugging and insight tool (dev only)

### Frontend
- **Blade Templates** - Server-side rendering
- **Alpine.js 3.x** - Minimal client-side interactivity
- **Tailwind CSS** - Styling framework
- **Chart.js** - Data visualization

### Auditing Tools
- **Spatie Crawler** - Site crawling and link validation
- **Puppeteer (Node.js)** - Headless browser automation
- **Browsershot** - Laravel wrapper for Puppeteer
- **Google Lighthouse CLI** - Performance auditing
- **Pixelmatch** - Screenshot comparison

### Infrastructure
- **Docker** - Containerization for consistent environments
- **Nginx** - Web server

## ✅ What's Been Built

### Infrastructure & Configuration
- ✅ Complete Docker setup (docker-compose.yml, Dockerfiles for app/nginx)
- ✅ Nginx configuration with PHP-FPM
- ✅ Environment configuration (.env with PostgreSQL, Redis, audit settings)
- ✅ PHP configuration (memory limits, upload sizes, timeouts)

### Dependencies
- ✅ All PHP packages installed:
  - Laravel Breeze (authentication)
  - Laravel Horizon (queue management)
  - Laravel Telescope (debugging)
  - Spatie Crawler (site crawling)
  - Browsershot (Puppeteer integration)
  - DomPDF (PDF generation)
  - Predis (Redis client)

- ✅ Frontend dependencies configured:
  - Alpine.js
  - Chart.js
  - Pixelmatch

### Database Layer
- ✅ Complete database schema with 6 core tables:
  - `audits` - Main audit records
  - `pages` - Crawled pages
  - `issues` - Discovered problems
  - `performance_metrics` - Core Web Vitals and Lighthouse scores
  - `links` - Link validation results
  - `checkout_steps` - Checkout flow analysis

- ✅ All Eloquent models with relationships:
  - Audit model with UUIDs, relationships, helper methods
  - Page model with full schema
  - Issue model with categorization
  - PerformanceMetric model for metrics tracking
  - Link model for broken link detection
  - CheckoutStep model for checkout analysis
  - User model with audits relationship

### Authentication
- ✅ Laravel Breeze installed with Blade stack
- ✅ User authentication system configured
- ✅ Registration/login/password reset views
- ✅ Auth routes and middleware

### Queue Jobs
- ✅ CrawlSiteJob - Main orchestrator with error handling
- ✅ AnalyzePageJob - Page analysis and SEO validation
- ✅ PerformanceAnalysisJob - Lighthouse integration (mobile & desktop)
- ✅ ValidateLinksJob - Link checking and broken link detection
- ✅ TestCheckoutFlowJob - Checkout flow testing
- ✅ AggregateResultsJob - Results compilation and scoring
- ✅ Error handling and retry logic (2-3 retries per job)
- ✅ Job timeouts configured (300 seconds)
- ✅ Issue detection and severity classification

### Services
- ✅ AuditService - Main business logic
- ✅ PuppeteerService - Browser automation
- ✅ LighthouseService - Performance testing
- ✅ CrawlerService - Site crawling wrapper
- ✅ ScoringService - Calculate audit scores
- ✅ ReportService - Generate reports

### Controllers & Routes
- ✅ AuditController - CRUD operations
- ✅ ResultsController - Display results
- ✅ ReportController - PDF generation
- ✅ DashboardController - Overview
- ✅ AuditPolicy - Authorization for user-owned audits

### Views
- ✅ Dashboard layout - Stats overview and recent audits
- ✅ Audit creation form - Simple URL and max pages input
- ✅ Audit index - Paginated list with filtering
- ✅ Audit results dashboard - Overview with score and quick links
- ✅ Issues list (filterable) - Searchable by category, severity, keyword
- ✅ Performance summary - Core Web Vitals and Lighthouse scores by device
- ✅ Checkout flow results - Step-by-step checkout analysis
- ✅ Broken links report - Link validation with filtering
- ✅ Historical comparison - Compare two audits from same domain

## 🚧 What Needs to Be Built

### Configuration
- ✅ Horizon configuration and dashboard
- ✅ Telescope configuration (dev only)
- ✅ Custom audit configuration file
- ✅ Integrate actual Spatie Crawler (implemented in CrawlerService)
- ✅ Integrate actual Browsershot (implemented in PuppeteerService)
- ✅ Integrate actual Lighthouse CLI (implemented in LighthouseService)

### External Tool Integration
- ✅ Spatie Crawler integration in CrawlSiteJob
- ✅ Browsershot screenshot capture in AnalyzePageJob
- ✅ Lighthouse CLI execution in PerformanceAnalysisJob
- ✅ HTTP client for link validation in ValidateLinksJob
- ✅ Puppeteer automation in TestCheckoutFlowJob

### Enhanced Logging
- ✅ Dedicated audit log channel (`storage/logs/audit.log`)
- ✅ Comprehensive progress tracking with visual indicators
- ✅ Real-time monitoring capability

### Testing
- [ ] Unit tests for models
- [ ] Feature tests for audit workflows
- [ ] Integration tests for external tools
- [ ] End-to-end audit workflow testing

## 🚀 Quick Start

### Local Development (Docker)

**Prerequisites:** Docker and Docker Compose

1. **Build and start containers**
   ```bash
   docker-compose up -d --build
   ```

2. **Install dependencies**
   ```bash
   docker-compose exec app composer install
   docker-compose exec app npm install
   docker-compose exec app npm run build
   ```

3. **Run migrations**
   ```bash
   docker-compose exec app php artisan migrate
   ```

4. **Access the application**
   - Application: http://localhost:8000

**Development mode (watch assets):**
```bash
docker-compose exec app npm run dev
```

**Start queue processing:**
```bash
# Using Horizon (recommended)
docker-compose exec app php artisan horizon

# Access Horizon dashboard at http://localhost:8000/horizon
```

**Monitor audit progress:**
```bash
# Watch the dedicated audit log in real-time
docker-compose exec app tail -f storage/logs/audit.log
```

### Cloudways Deployment

**Prerequisites:**
- Cloudways server with at least 2GB RAM (4GB+ recommended for Chromium)
- Node.js 18+ already installed (check with `node --version`)
- At least 500MB free disk space for Puppeteer/Chromium
- SSH access to your Cloudways server

**Note:** Cloudways managed hosting does not provide sudo/root access for apt packages, so we'll use Puppeteer's bundled Chromium instead of installing Chromium via apt. Global npm packages can be installed normally.

#### 1. Initial Setup

```bash
# SSH into your Cloudways server
ssh master@your-server-ip
cd /home/master/applications/your-app/public_html

# Install Composer dependencies
composer install --optimize-autoloader --no-dev

# Install NPM dependencies (includes Puppeteer with bundled Chromium)
npm install

# Build frontend assets
npm run build

# Set up environment
cp .env.example .env
php artisan key:generate

# Configure your .env file
nano .env
# Set database credentials, Redis, and other production settings
```

#### 2. Install Audit Dependencies (Puppeteer & Lighthouse)

```bash
# Install Puppeteer and Lighthouse globally
# This will download Chromium (~300MB) - may take a few minutes
npm install -g puppeteer lighthouse

# Verify installation
lighthouse --version
npm list -g puppeteer

# Check Puppeteer's Chromium location
ls -la ~/.cache/puppeteer/chrome/

# Test Lighthouse works
lighthouse https://example.com \
  --output=json \
  --output-path=./test-report.json \
  --quiet \
  --chrome-flags="--headless --no-sandbox --disable-gpu"
```

#### 3. Configure Environment Variables

Add these to your `.env` file:

```bash
# Database
DB_CONNECTION=pgsql
DB_HOST=your-cloudways-db-host
DB_PORT=5432
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue
QUEUE_CONNECTION=redis

# Audit Tool Configuration
# Find your lighthouse path with: npm prefix -g
# Then use: {npm-prefix}/bin/lighthouse
LIGHTHOUSE_PATH=/usr/bin/lighthouse
# Or dynamically: LIGHTHOUSE_PATH=$(npm prefix -g)/bin/lighthouse
# Do NOT set PUPPETEER_EXECUTABLE_PATH - uses bundled Chromium
# Do NOT set PUPPETEER_SKIP_CHROMIUM_DOWNLOAD

# App
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
```

#### 4. Run Database Migrations

```bash
php artisan migrate --force
```

#### 5. Configure Queue Workers

**Important:** Cloudways doesn't provide sudo access. Choose one of these options:

**Option A: Contact Cloudways Support (Recommended)**

Request Cloudways support to set up Supervisor with this configuration:

```ini
[program:laravel-horizon]
process_name=%(program_name)s
command=php /home/master/applications/your-app/public_html/artisan horizon
autostart=true
autorestart=true
user=master
redirect_stderr=true
stdout_logfile=/home/master/applications/your-app/public_html/storage/logs/horizon.log
stopwaitsecs=3600
```

**Option B: Use Cloudways Application Panel**

Check if your Cloudways panel has "Supervisor" or "Background Processes" settings and add:
```
php artisan horizon
```

**Option C: Use Cron Job (Workaround)**

Add to crontab (`crontab -e`):

```bash
# Keep Horizon running (restarts if stopped)
* * * * * cd /home/master/applications/your-app/public_html && php artisan horizon >> /dev/null 2>&1 &

# Alternative: Simple queue worker
# * * * * * cd /home/master/applications/your-app/public_html && php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 >> /dev/null 2>&1 &
```

**Verify Horizon is running:**

```bash
# Check if process is running
ps aux | grep horizon

# Or check logs
tail -f storage/logs/horizon.log
```

Access Horizon dashboard at: `https://your-domain.com/horizon`

#### 6. Configure Cron Job for Scheduler

```bash
crontab -e
```

Add this line (adjust path for your application):

```bash
* * * * * cd /home/master/applications/your-app/public_html && php artisan schedule:run >> /dev/null 2>&1
```

#### 7. Set Permissions

```bash
# Ensure storage and cache directories are writable
chmod -R 775 storage bootstrap/cache
chown -R master:www-data storage bootstrap/cache
```

#### 8. Optimize for Production

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache
```

#### 9. Test the Audit System

```bash
# Monitor audit logs in real-time
tail -f storage/logs/audit.log

# In another terminal, trigger a test audit via Tinker
php artisan tinker
```

In Tinker:
```php
$audit = \App\Models\Audit::create([
    'user_id' => 1, // Use your user ID
    'url' => 'https://example.com',
    'status' => 'pending',
    'max_pages' => 5
]);

dispatch(new \App\Jobs\CrawlSiteJob($audit));
```

Watch the logs to ensure jobs are processing correctly.

#### Troubleshooting

**Chromium crashes or times out:**
- Increase server memory (need at least 2GB, 4GB+ recommended)
- Check Chrome flags in PuppeteerService include `--no-sandbox --disable-dev-shm-usage`

**"Lighthouse command not found":**
- Verify path in .env: `LIGHTHOUSE_PATH=/usr/local/bin/lighthouse`
- Check installation: `which lighthouse` or `npm list -g lighthouse`

**Queue jobs not processing:**
- Check Horizon status: `sudo supervisorctl status laravel-horizon`
- View Horizon logs: `tail -f storage/logs/horizon.log`
- Restart Horizon: `sudo supervisorctl restart laravel-horizon`

**Permission errors:**
- Ensure storage is writable: `chmod -R 775 storage`
- Check file ownership: `chown -R master:www-data storage`

## ✨ Key Features

### Automated Auditing
- **Real Site Crawling**: Spatie Crawler discovers pages automatically up to configured limit
- **Performance Testing**: Lighthouse CLI audits for both mobile and desktop devices
- **Screenshot Capture**: Full-page screenshots using Browsershot/Puppeteer
- **Link Validation**: HTTP client checks all internal, external, and asset links
- **Checkout Testing**: Automated checkout flow navigation and screenshot capture

### Issue Detection
- **SEO Issues**: Missing/invalid meta tags, titles, H1 tags
- **Performance Issues**: Poor Core Web Vitals (LCP, CLS, FID)
- **Broken Links**: 404s and other HTTP errors
- **Checkout Problems**: Complex flows, too many form fields

### Monitoring & Reporting
- **Real-time Progress**: Dedicated audit log with visual indicators
- **Horizon Dashboard**: Queue monitoring at `/horizon`
- **Historical Comparison**: Track improvements over time
- **Multiple Export Formats**: PDF, CSV, and JSON reports

### Scoring System
Weighted scoring across 5 categories:
- Performance (30%)
- Mobile Experience (25%)
- SEO (20%)
- Checkout Flow (15%)
- Link Health (10%)

## 📋 Database Schema

See `ecommerce-audit-tool-requirements.md` for complete schema documentation.

## 🎨 Features

### Implemented ✅
- ✅ Performance Analysis (Core Web Vitals, Lighthouse)
- ✅ Checkout Testing
- ✅ Mobile Responsiveness Testing
- ✅ SEO Analysis
- ✅ Link Validation
- ✅ Historical Comparisons
- ✅ PDF/CSV/JSON Report Generation
- ✅ Real-time Audit Monitoring
- ✅ Queue Management with Horizon

### Planned 📋
- [ ] AI-powered issue prioritization
- [ ] Slack/email notifications
- [ ] Custom audit templates
- [ ] Multi-site batch auditing
- [ ] API endpoints for third-party integrations

## 📝 Development Status

**Current Phase:** Production Ready (~90% Done)

### Completed ✅
- Infrastructure & Docker setup
- Database schema & migrations
- Eloquent models with relationships
- Authentication (Laravel Breeze)
- Complete queue job architecture (6 jobs)
- Error handling & retry logic
- Issue detection & severity classification
- Service layer implementation (6 services)
- Controllers & routes (4 controllers + policy)
- Blade views & UI (12+ views with components)
- **External tool integration:**
  - ✅ Spatie Crawler for site discovery
  - ✅ Browsershot/Puppeteer for screenshots and automation
  - ✅ Lighthouse CLI for performance audits
  - ✅ Laravel HTTP client for link validation
- Horizon & Telescope configuration
- **Enhanced audit logging system:**
  - ✅ Dedicated log channel (`storage/logs/audit.log`)
  - ✅ Visual progress indicators (✓ ✗ →)
  - ✅ Real-time monitoring support

### In Progress 🚧
- Testing & validation
- System dependency setup (Node.js, Chromium, Lighthouse CLI)

### Next Steps 📋
1. Install system dependencies:
   - Node.js 18+ (for Puppeteer/Lighthouse)
   - Chromium browser
   - Lighthouse CLI: `npm install -g lighthouse`
2. Write tests (unit, feature, integration)
3. Test complete audit flow end-to-end
4. Production deployment to Cloudways
5. Performance optimization and scaling

---

**Version:** 1.0.0-alpha
**Status:** Production Ready (Testing Phase)
**Last Updated:** December 24, 2025
