#!/bin/bash

# Exit on error
set -e

# Configuration
AWS_REGION="us-east-1"  # Change as needed
ECR_REPOSITORY_NAME="rc-importer"
IMAGE_TAG=$(git rev-parse --short HEAD)  # Use short commit hash as tag
CLUSTER_NAME="rc-importer-cluster"

# Check if AWS CLI is installed
if ! command -v aws &> /dev/null; then
    echo "AWS CLI is not installed. Please install it first."
    exit 1
fi

# Check authentication
echo "Checking AWS authentication..."
aws sts get-caller-identity

# Create ECR repository if it doesn't exist
echo "Ensuring ECR repository exists..."
aws ecr describe-repositories --repository-names $ECR_REPOSITORY_NAME --region $AWS_REGION || aws ecr create-repository --repository-name $ECR_REPOSITORY_NAME --region $AWS_REGION

# Get ECR repository URI
ECR_REPOSITORY_URI=$(aws ecr describe-repositories --repository-names $ECR_REPOSITORY_NAME --region $AWS_REGION --query 'repositories[0].repositoryUri' --output text)
echo "ECR Repository URI: $ECR_REPOSITORY_URI"

# Authenticate Docker to ECR
echo "Authenticating Docker to ECR..."
aws ecr get-login-password --region $AWS_REGION | docker login --username AWS --password-stdin $ECR_REPOSITORY_URI

# Build Docker image
echo "Building Docker image..."
docker build -t $ECR_REPOSITORY_NAME:$IMAGE_TAG .

# Tag Docker image for ECR
echo "Tagging Docker image..."
docker tag $ECR_REPOSITORY_NAME:$IMAGE_TAG $ECR_REPOSITORY_URI:$IMAGE_TAG
docker tag $ECR_REPOSITORY_NAME:$IMAGE_TAG $ECR_REPOSITORY_URI:latest

# Push Docker image to ECR
echo "Pushing Docker image to ECR..."
docker push $ECR_REPOSITORY_URI:$IMAGE_TAG
docker push $ECR_REPOSITORY_URI:latest

# Update Kubernetes deployment file with the ECR URI and image tag
echo "Updating Kubernetes deployment file..."
sed -i "s|\${ECR_REPOSITORY_URI}|$ECR_REPOSITORY_URI|g" kubernetes/deployment.yaml
sed -i "s|\${IMAGE_TAG}|$IMAGE_TAG|g" kubernetes/deployment.yaml

# Check if kubectl is configured
echo "Checking kubectl configuration..."
if ! kubectl config current-context &> /dev/null; then
    echo "Configuring kubectl for EKS cluster $CLUSTER_NAME..."
    aws eks update-kubeconfig --name $CLUSTER_NAME --region $AWS_REGION
fi

# Apply Kubernetes secrets (warning: initial setup requires manual editing of secrets.yaml)
if [ "$1" == "--apply-secrets" ]; then
    echo "Applying Kubernetes secrets..."
    kubectl apply -f kubernetes/secrets.yaml
fi

# Apply Kubernetes deployment
echo "Applying Kubernetes deployment..."
kubectl apply -f kubernetes/deployment.yaml

echo "Deployment completed successfully!"