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

## 🚧 What Needs to Be Built

### Authentication
- [ ] Install and configure Laravel Breeze
- [ ] Set up user authentication
- [ ] Create user registration/login views

### Queue Jobs
- [ ] CrawlSiteJob - Main orchestrator
- [ ] AnalyzePageJob - Page analysis
- [ ] PerformanceAnalysisJob - Lighthouse integration
- [ ] ValidateLinksJob - Link checking
- [ ] TestCheckoutFlowJob - Checkout testing
- [ ] AggregateResultsJob - Results compilation

### Services
- [ ] AuditService - Main business logic
- [ ] PuppeteerService - Browser automation
- [ ] LighthouseService - Performance testing
- [ ] CrawlerService - Site crawling wrapper
- [ ] ScoringService - Calculate audit scores
- [ ] ReportService - Generate reports

### Controllers & Routes
- [ ] AuditController - CRUD operations
- [ ] ResultsController - Display results
- [ ] ReportController - PDF generation
- [ ] DashboardController - Overview

### Views
- [ ] Dashboard layout
- [ ] Audit creation form
- [ ] Audit results dashboard
- [ ] Issues list (filterable)
- [ ] Performance summary
- [ ] Checkout flow results
- [ ] Broken links report
- [ ] Historical comparison

### Configuration
- [ ] Horizon configuration and dashboard
- [ ] Queue worker configuration
- [ ] Telescope configuration (dev only)
- [ ] Audit configuration file
- [ ] Puppeteer/Lighthouse settings

### Testing
- [ ] Unit tests for models
- [ ] Feature tests for audit workflows
- [ ] Integration tests for external tools

## 🚀 Quick Start

### Prerequisites
- Docker and Docker Compose
- Git

### Installation

1. **Build and start Docker containers**
   ```bash
   docker-compose up -d --build
   ```

2. **Install dependencies inside container**
   ```bash
   docker-compose exec app composer install
   docker-compose exec app npm install
   ```

3. **Run migrations**
   ```bash
   docker-compose exec app php artisan migrate
   ```

4. **Build frontend assets**
   ```bash
   docker-compose exec app npm run build
   ```

5. **Access the application**
   - Application: http://localhost:8000

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

**Current Phase:** Foundation Complete
**Next Phase:** Core Functionality Implementation

---

**Version:** 1.0.0-alpha
**Status:** In Development
**Last Updated:** December 23, 2025
