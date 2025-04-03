# Docker and Kubernetes Deployment Guide

This guide provides detailed instructions for setting up, deploying, and managing the RC Importer application using Docker and Kubernetes on AWS.

## Table of Contents

- [Local Development Setup](#local-development-setup)
- [AWS Setup](#aws-setup)
- [Kubernetes Deployment](#kubernetes-deployment)
- [Maintenance and Operations](#maintenance-and-operations)
- [Troubleshooting](#troubleshooting)

## Local Development Setup

### Prerequisites

- Docker installed (20.10.x or later)
- Docker Compose installed (2.x or later)
- Git

### Setup Steps

1. **Clone the repository**

   ```bash
   git clone https://github.com/your-repo/rc-importer.git
   cd rc-importer
   ```

2. **Prepare environment file**

   ```bash
   cp .env.example .env
   ```

   Edit `.env` file to set the following variables:
   
   ```
   APP_NAME=RCImporter
   APP_ENV=local
   APP_KEY=  # Will be generated in step 5
   APP_DEBUG=true
   APP_URL=http://localhost:8080
   
   DB_CONNECTION=mysql
   DB_HOST=mysql
   DB_PORT=3306
   DB_DATABASE=rc_viewer
   DB_USERNAME=rc_user
   DB_PASSWORD=rc_password
   
   CACHE_DRIVER=redis
   SESSION_DRIVER=redis
   REDIS_HOST=redis
   ```

3. **Start the containers**

   ```bash
   docker-compose up -d
   ```

4. **Install dependencies**

   ```bash
   docker-compose exec app composer install
   docker-compose exec app npm install
   ```

5. **Generate application key**

   ```bash
   docker-compose exec app php artisan key:generate
   ```

6. **Run database migrations**

   ```bash
   docker-compose exec app php artisan migrate:fresh --seed
   ```

7. **Build frontend assets**

   ```bash
   docker-compose exec app npm run build
   ```

8. **Access the application**

   Open [http://localhost:8080](http://localhost:8080) in your browser.

### Running Tests

```bash
docker-compose exec app php artisan test
```

### Stopping the Environment

```bash
docker-compose down
```

To remove volumes (database data):

```bash
docker-compose down -v
```

## AWS Setup

### Prerequisites

- AWS CLI installed and configured
- IAM permissions for ECR, EKS, and related services
- kubectl installed

### Create an ECR Repository

1. **Create an ECR repository for the application**

   ```bash
   aws ecr create-repository \
       --repository-name rc-importer \
       --region us-east-1
   ```

   Note the repository URI from the output.

2. **Authenticate Docker to ECR**

   ```bash
   aws ecr get-login-password --region us-east-1 | docker login --username AWS --password-stdin <your-account-id>.dkr.ecr.us-east-1.amazonaws.com
   ```

### Set Up EKS Cluster

If you don't have an EKS cluster already:

1. **Create EKS cluster**

   ```bash
   aws eks create-cluster \
       --name rc-importer-cluster \
       --role-arn arn:aws:iam::<your-account-id>:role/EksClusterRole \
       --resources-vpc-config subnetIds=subnet-xxxx,subnet-yyyy,subnet-zzzz,securityGroupIds=sg-xxxxxxxx \
       --kubernetes-version 1.27
   ```

2. **Configure kubectl for your cluster**

   ```bash
   aws eks update-kubeconfig --name rc-importer-cluster --region us-east-1
   ```

## Kubernetes Deployment

### Prepare Kubernetes Secrets

1. **Generate base64 encoded secrets**

   ```bash
   # Generate a Laravel app key
   APP_KEY=$(docker-compose exec -T app php artisan key:generate --show)
   
   # Encode secrets to base64
   echo -n "$APP_KEY" | base64
   echo -n "your-db-host" | base64
   echo -n "your-db-name" | base64
   echo -n "your-db-username" | base64
   echo -n "your-db-password" | base64
   echo -n "your-redis-host" | base64
   ```

2. **Update the secrets file**

   Edit `kubernetes/secrets.yaml` and replace the placeholder values with your base64 encoded values:

   ```yaml
   apiVersion: v1
   kind: Secret
   metadata:
     name: rc-importer-secrets
   type: Opaque
   data:
     app-key: <base64-encoded-app-key>
     db-host: <base64-encoded-db-host>
     db-database: <base64-encoded-db-name>
     db-username: <base64-encoded-db-username>
     db-password: <base64-encoded-db-password>
     redis-host: <base64-encoded-redis-host>
   ```

### Deploy to Kubernetes

1. **Update the deployment configuration**

   Edit `kubernetes/deployment.yaml` to update:
   
   - The Ingress host: Replace `rc-importer.example.com` with your domain
   - Resource limits if needed based on your workload
   - Any other environment-specific configurations

2. **Deploy using the script**

   For the first deployment:
   
   ```bash
   ./deploy-aws.sh --apply-secrets
   ```
   
   For subsequent deployments:
   
   ```bash
   ./deploy-aws.sh
   ```

3. **Verify the deployment**

   ```bash
   kubectl get pods
   kubectl get svc
   kubectl get ingress
   ```

4. **Check application logs**

   ```bash
   kubectl logs -l app=rc-importer
   ```

### Manual Deployment Process

If you prefer to deploy manually or need to understand what the script does:

1. **Build the Docker image**

   ```bash
   docker build -t rc-importer:latest .
   ```

2. **Tag the image for ECR**

   ```bash
   docker tag rc-importer:latest <your-account-id>.dkr.ecr.us-east-1.amazonaws.com/rc-importer:latest
   ```

3. **Push the image to ECR**

   ```bash
   docker push <your-account-id>.dkr.ecr.us-east-1.amazonaws.com/rc-importer:latest
   ```

4. **Apply Kubernetes configurations**

   ```bash
   kubectl apply -f kubernetes/secrets.yaml
   kubectl apply -f kubernetes/deployment.yaml
   ```

## Maintenance and Operations

### Scaling the Application

To scale the number of replicas:

```bash
kubectl scale deployment rc-importer --replicas=3
```

### Running Database Migrations

After deploying an update with database migrations:

```bash
# Get a pod name
POD_NAME=$(kubectl get pod -l app=rc-importer -o jsonpath="{.items[0].metadata.name}")

# Run migrations
kubectl exec $POD_NAME -- php artisan migrate --force
```

### Viewing Application Logs

```bash
kubectl logs -l app=rc-importer --tail=100 -f
```

### Updating the Application

1. Make code changes
2. Run the deployment script:
   ```bash
   ./deploy-aws.sh
   ```

### Accessing the Application Shell

```bash
POD_NAME=$(kubectl get pod -l app=rc-importer -o jsonpath="{.items[0].metadata.name}")
kubectl exec -it $POD_NAME -- /bin/sh
```

## Troubleshooting

### Common Issues and Solutions

1. **Pod is in CrashLoopBackOff state**

   Check the logs:
   ```bash
   kubectl logs -l app=rc-importer
   ```

2. **Database connection issues**

   Verify secrets are correctly configured:
   ```bash
   kubectl describe secret rc-importer-secrets
   ```
   
   Check if the pod can reach the database:
   ```bash
   POD_NAME=$(kubectl get pod -l app=rc-importer -o jsonpath="{.items[0].metadata.name}")
   kubectl exec $POD_NAME -- ping <your-db-host>
   ```

3. **Health check failures**

   Check if the health endpoint is accessible inside the pod:
   ```bash
   POD_NAME=$(kubectl get pod -l app=rc-importer -o jsonpath="{.items[0].metadata.name}")
   kubectl exec $POD_NAME -- curl http://localhost/health
   ```

4. **Nginx or PHP-FPM issues**

   Check Nginx and PHP-FPM logs:
   ```bash
   POD_NAME=$(kubectl get pod -l app=rc-importer -o jsonpath="{.items[0].metadata.name}")
   kubectl exec $POD_NAME -- cat /var/log/nginx/error.log
   kubectl exec $POD_NAME -- cat /var/log/php-fpm.log
   ```

5. **Image pull errors**

   Check if the image exists in ECR and if the cluster has permissions:
   ```bash
   aws ecr describe-images --repository-name rc-importer
   ```

### Debugging Deployments

To debug deployment issues:

```bash
kubectl describe deployment rc-importer
kubectl describe pod -l app=rc-importer
```

### Checking Resource Usage

```bash
kubectl top pods -l app=rc-importer
```

---

For additional support, contact the development team or refer to the project documentation.