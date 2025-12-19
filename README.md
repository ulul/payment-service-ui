# Payment Service UI

User interface for payment service

## Quick Start

- make sure payment service backend already started

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js & npm (for frontend assets)

1. **Clone the repository**
   ```bash
   git clone https://github.com/ulul/payment-service-ui.git
   cd payment-service-ui
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   npm run build
   ```

3. **Setup environment configuration**
   ```bash
   cp .env.example .env
   ``

4. **Update environment variables**
   - Update `.env`:
   ```
   API_BASE_URL=http://localhost:8000
   API_CALL_TIMEOUT=15 
   MIDTRANS_CLIENT_KEY=midtrans_client_key
   ```
5. **Start the application**
   ```bash
   php artisan serve --port=8001
   ```
   Application will be available at `http://localhost:8001`
   