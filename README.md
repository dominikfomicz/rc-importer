# RC Importer
### Importer for RCLootCouncil files

This application imports and displays loot data from World of Warcraft's RCLootCouncil addon.

## Local Development

### Using Docker Compose

```bash
# Clone the repository
git clone https://github.com/your-repo/rc-importer.git
cd rc-importer

# Copy environment file
cp .env.example .env

# Build and start the containers
docker-compose up -d

# Install dependencies
docker-compose exec app composer install
docker-compose exec app npm install

# Generate application key
docker-compose exec app php artisan key:generate

# Run migrations and seed the database
docker-compose exec app php artisan migrate --seed

# Build frontend assets
docker-compose exec app npm run build
```

The application will be available at http://localhost:8080

### Running Tests

```bash
docker-compose exec app php artisan test
```

## AWS & Kubernetes Deployment

The application is configured for deployment to AWS EKS (Elastic Kubernetes Service).

### Prerequisites

- AWS CLI configured with appropriate permissions
- kubectl installed and configured
- Access to an EKS cluster

### Deployment Steps

1. Edit the Kubernetes secrets file with your environment values:

```bash
# Edit kubernetes/secrets.yaml with your base64 encoded values
nano kubernetes/secrets.yaml
```

2. Run the deployment script:

```bash
# For first deployment with secrets
./deploy-aws.sh --apply-secrets

# For subsequent deployments
./deploy-aws.sh
```

## Test User

For local development, you can use the following test user:

```
Email: test@mail.gov
Password: 12345678
```