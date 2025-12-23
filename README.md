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
- [ ] Horizon configuration and dashboard
- [ ] Telescope configuration (dev only)
- ✅ Custom audit configuration file
- ✅ Integrate actual Spatie Crawler (implemented in CrawlerService)
- ✅ Integrate actual Browsershot (implemented in PuppeteerService)
- ✅ Integrate actual Lighthouse CLI (implemented in LighthouseService)

### Testing
- [ ] Unit tests for models
- [ ] Feature tests for audit workflows
- [ ] Integration tests for external tools

### External Tool Integration (Placeholders Ready)
- [ ] Complete Spatie Crawler integration in CrawlSiteJob
- [ ] Complete Browsershot screenshot capture in AnalyzePageJob
- [ ] Complete Lighthouse CLI execution in PerformanceAnalysisJob
- [ ] Complete HTTP client for link validation in ValidateLinksJob
- [ ] Complete Puppeteer automation in TestCheckoutFlowJob

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

### Cloudways Deployment

1. **Initial Setup**
   ```bash
   # SSH into your Cloudways server
   cd /path/to/application

   # Install Composer dependencies
   composer install --optimize-autoloader --no-dev

   # Install NPM dependencies and build assets
   npm install
   npm run build

   # Set up environment
   cp .env.example .env
   php artisan key:generate

   # Run migrations
   php artisan migrate --force
   ```

2. **Configure Queue Workers**
   - Set up Supervisor to run `php artisan queue:work redis --tries=3 --timeout=300`
   - Configure Laravel Horizon (optional, better queue monitoring)

3. **Configure Scheduler**
   - Add to crontab: `* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1`

4. **Install System Dependencies**
   - Node.js 18+ (for Puppeteer/Lighthouse)
   - Chromium browser
   - Install via: `npm install -g puppeteer lighthouse`

5. **Environment Variables**
   - Configure `.env` with production database credentials
   - Set `PUPPETEER_EXECUTABLE_PATH` to Chromium location
   - Set `LIGHTHOUSE_PATH=/usr/local/bin/lighthouse`

## 📋 Database Schema

See `ecommerce-audit-tool-requirements.md` for complete schema documentation.

## 🎨 Features (Planned)

- Performance Analysis (Core Web Vitals, Lighthouse)
- Checkout Testing
- Mobile Responsiveness
- SEO Analysis
- Link Validation
- Historical Comparisons
- PDF Report Generation

## 📝 Development Status

**Current Phase:** Queue Architecture Complete (~45% Done)

### Completed ✅
- Infrastructure & Docker setup
- Database schema & migrations
- Eloquent models with relationships
- Authentication (Laravel Breeze)
- Complete queue job architecture (6 jobs)
- Error handling & retry logic
- Issue detection & severity classification

### In Progress 🚧
- Service layer implementation
- Controllers & routes
- Blade views & UI

### Next Steps 📋
1. Implement AuditController & routes
2. Create service classes (AuditService, PuppeteerService, LighthouseService)
3. Build basic Blade views
4. Integrate external tools (Spatie Crawler, Browsershot, Lighthouse)
5. Configure Horizon for queue monitoring

---

**Version:** 1.0.0-alpha
**Status:** In Development
**Last Updated:** December 23, 2025
