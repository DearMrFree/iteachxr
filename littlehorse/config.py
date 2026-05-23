"""
LittleHorse Configuration and Deployment Guide

This file contains all configuration needed to run LittleHorse in:
1. Local development (Docker Compose)
2. Production (Kubernetes)
3. Testing environments
"""

import os
from typing import Optional

# ============================================================================
# DEVELOPMENT ENVIRONMENT CONFIGURATION
# ============================================================================

class DevelopmentConfig:
    """Configuration for local development with Docker Compose."""
    
    # LittleHorse Server
    LITTLEHORSE_HOST = os.getenv("LHC_API_HOST", "localhost")
    LITTLEHORSE_PORT = int(os.getenv("LHC_API_PORT", 2023))
    LITTLEHORSE_PROTOCOL = os.getenv("LHC_API_PROTOCOL", "PLAINTEXT")
    
    # Database
    DATABASE_URL = os.getenv(
        "DATABASE_URL",
        "postgresql://iteachxr_user:iteachxr_password_dev@localhost:5432/iteachxr"
    )
    
    # Kafka
    KAFKA_BOOTSTRAP_SERVERS = os.getenv(
        "KAFKA_BOOTSTRAP_SERVERS",
        "localhost:9092"
    )
    
    # API
    API_HOST = "0.0.0.0"
    API_PORT = 8000
    API_LOG_LEVEL = "info"
    
    # Monitoring
    PROMETHEUS_PORT = 9090
    GRAFANA_PORT = 3000


# ============================================================================
# PRODUCTION ENVIRONMENT CONFIGURATION
# ============================================================================

class ProductionConfig:
    """Configuration for production Kubernetes deployment."""
    
    # LittleHorse Cluster (with mTLS)
    LITTLEHORSE_HOST = os.getenv("LHC_API_HOST", "littlehorse-service.default.svc.cluster.local")
    LITTLEHORSE_PORT = int(os.getenv("LHC_API_PORT", 2023))
    LITTLEHORSE_PROTOCOL = os.getenv("LHC_API_PROTOCOL", "TLS")
    LITTLEHORSE_CA_CERT = os.getenv("LHC_CA_CERT", "/etc/ssl/littlehorse/ca.crt")
    LITTLEHORSE_CLIENT_CERT = os.getenv("LHC_CLIENT_CERT", "/etc/ssl/littlehorse/client.crt")
    LITTLEHORSE_CLIENT_KEY = os.getenv("LHC_CLIENT_KEY", "/etc/ssl/littlehorse/client.key")
    
    # Database (external managed)
    DATABASE_URL = os.getenv("DATABASE_URL", "")
    
    # Kafka (external managed)
    KAFKA_BOOTSTRAP_SERVERS = os.getenv("KAFKA_BOOTSTRAP_SERVERS", "")
    
    # API
    API_HOST = "0.0.0.0"
    API_PORT = 8000
    API_WORKERS = int(os.getenv("API_WORKERS", 4))
    API_LOG_LEVEL = "warning"
    
    # Security
    SECRET_KEY = os.getenv("SECRET_KEY", "")
    CORS_ORIGINS = os.getenv("CORS_ORIGINS", "https://iteachxr.com").split(",")


# ============================================================================
# FEATURE FLAGS
# ============================================================================

FEATURE_FLAGS = {
    "use_littlehorse_onboarding": {
        "enabled": True,
        "rollout_percentage": int(os.getenv("ROLLOUT_ONBOARDING", 10)),
        "regions": os.getenv("ROLLOUT_REGIONS", "").split(",") if os.getenv("ROLLOUT_REGIONS") else [],
        "rollback_trigger": "error_rate > 0.05"
    },
    "use_littlehorse_progression": {
        "enabled": True,
        "rollout_percentage": int(os.getenv("ROLLOUT_PROGRESSION", 5)),
        "regions": [],
        "rollback_trigger": "error_rate > 0.03"
    },
    "use_littlehorse_assessment": {
        "enabled": False,
        "rollout_percentage": 0,
        "regions": [],
        "rollback_trigger": None
    },
    "enable_auto_correction": {
        "enabled": True,
        "auto_deploy_percentage": int(os.getenv("AUTO_DEPLOY_PCT", 60)),  # % of fixes auto-deployed
        "require_approval": os.getenv("AUTO_CORRECTION_APPROVAL", "high-impact") == "all"
    }
}


# ============================================================================
# DOCKER COMPOSE QUICK START
# ============================================================================

DOCKER_COMPOSE_SETUP = """
# Quick Start Guide for Local Development

## Prerequisites
- Docker & Docker Compose installed
- Git cloned: https://github.com/DearMrFree/iteachxr

## Step 1: Start all services
docker-compose up -d

## Step 2: Wait for services to be healthy
docker-compose ps
# All services should show "healthy" status

## Step 3: Verify LittleHorse server
curl http://localhost:8080  # Should show dashboard

## Step 4: Install LittleHorse CLI
brew install littlehorse-enterprises/lh/lhctl

## Step 5: Deploy workflows to server
lhctl deploy wfSpec littlehorse/workflows/core_workflows.json

## Step 6: Start API bridge
python -m uvicorn api.littlehorse_bridge:app --host 0.0.0.0 --port 8000

## Step 7: Test with a workflow execution
curl -X POST http://localhost:8000/api/v1/students/enroll \\
  -H "Content-Type: application/json" \\
  -d '{
    "student_id": "stu_test_001",
    "name": "Test Student",
    "learning_style": "visual",
    "interests": ["science"],
    "age_group": "teen"
  }'

## Step 8: View workflow execution
# Dashboard: http://localhost:8080
# API Docs: http://localhost:8000/docs
# Grafana: http://localhost:3000 (admin/admin)

## Stopping services
docker-compose down

## View logs
docker-compose logs -f littlehorse
docker-compose logs -f api
docker-compose logs -f task-workers
"""


# ============================================================================
# KUBERNETES DEPLOYMENT MANIFESTS
# ============================================================================

KUBERNETES_MANIFESTS = """
apiVersion: v1
kind: Namespace
metadata:
  name: iteachxr
---
# ConfigMap for feature flags
apiVersion: v1
kind: ConfigMap
metadata:
  name: littlehorse-config
  namespace: iteachxr
data:
  feature-flags.json: |
    {
      "use_littlehorse_onboarding": {"enabled": true, "rollout_percentage": 10},
      "use_littlehorse_progression": {"enabled": true, "rollout_percentage": 5}
    }
---
# LittleHorse StatefulSet (3 replicas)
apiVersion: apps/v1
kind: StatefulSet
metadata:
  name: littlehorse
  namespace: iteachxr
spec:
  serviceName: littlehorse
  replicas: 3
  selector:
    matchLabels:
      app: littlehorse
  template:
    metadata:
      labels:
        app: littlehorse
    spec:
      containers:
      - name: littlehorse
        image: ghcr.io/littlehorse-enterprises/littlehorse/lh-standalone:latest
        ports:
        - containerPort: 2023
          name: grpc-api
        - containerPort: 8080
          name: dashboard
        env:
        - name: KAFKA_BOOTSTRAP_SERVERS
          value: "kafka-service.iteachxr.svc.cluster.local:9092"
        resources:
          requests:
            memory: "1Gi"
            cpu: "500m"
          limits:
            memory: "2Gi"
            cpu: "1000m"
        livenessProbe:
          exec:
            command: ["lhctl", "whoami"]
          initialDelaySeconds: 30
          periodSeconds: 10
---
# Task Worker Deployment (auto-scaling)
apiVersion: apps/v1
kind: Deployment
metadata:
  name: task-workers
  namespace: iteachxr
spec:
  replicas: 5
  strategy:
    type: RollingUpdate
    rollingUpdate:
      maxSurge: 1
      maxUnavailable: 1
  selector:
    matchLabels:
      app: task-worker
  template:
    metadata:
      labels:
        app: task-worker
    spec:
      containers:
      - name: worker
        image: iteachxr/task-workers:latest
        env:
        - name: LHC_API_HOST
          value: "littlehorse-service.iteachxr.svc.cluster.local"
        - name: DATABASE_URL
          valueFrom:
            secretKeyRef:
              name: db-credentials
              key: url
        resources:
          requests:
            memory: "512Mi"
            cpu: "250m"
          limits:
            memory: "1Gi"
            cpu: "500m"
---
# API Bridge Deployment
apiVersion: apps/v1
kind: Deployment
metadata:
  name: api-bridge
  namespace: iteachxr
spec:
  replicas: 2
  selector:
    matchLabels:
      app: api-bridge
  template:
    metadata:
      labels:
        app: api-bridge
    spec:
      containers:
      - name: api
        image: iteachxr/api-bridge:latest
        ports:
        - containerPort: 8000
        env:
        - name: LHC_API_HOST
          value: "littlehorse-service.iteachxr.svc.cluster.local"
        resources:
          requests:
            memory: "512Mi"
            cpu: "250m"
          limits:
            memory: "1Gi"
            cpu: "500m"
---
# HorizontalPodAutoscaler for Task Workers
apiVersion: autoscaling/v2
kind: HorizontalPodAutoscaler
metadata:
  name: task-worker-autoscaler
  namespace: iteachxr
spec:
  scaleTargetRef:
    apiVersion: apps/v1
    kind: Deployment
    name: task-workers
  minReplicas: 5
  maxReplicas: 50
  metrics:
  - type: Resource
    resource:
      name: cpu
      target:
        type: Utilization
        averageUtilization: 70
  - type: Resource
    resource:
      name: memory
      target:
        type: Utilization
        averageUtilization: 80
"""


# ============================================================================
# CLI COMMANDS
# ============================================================================

CLI_COMMANDS = """
# === Workflow Management ===

# Deploy a workflow
lhctl deploy wfSpec littlehorse/workflows/core_workflows.json

# List all workflows
lhctl list wfSpec

# Get workflow details
lhctl get wfSpec student-onboarding

# === Workflow Execution ===

# Run a workflow
lhctl run student-onboarding student-id stu_001 student-data '{"name":"Alice",...}'

# Check workflow execution status
lhctl get wfRun <wf_run_id>

# Get workflow run results
lhctl list taskRun <wf_run_id>

# === Monitoring ===

# Get system metrics
lhctl whoami  # Check server health

# Search active workflows
lhctl list wfRun

# Search completed workflows
lhctl search wfRun --status COMPLETED

# === Debugging ===

# Check task definition
lhctl get taskDef evaluate-initial-level

# List recent failures
lhctl search wfRun --status FAILED --limit 10

# Get task run details
lhctl get taskRun <wf_run_id> <task_run_id>
"""


# ============================================================================
# INITIALIZATION & DEPLOYMENT HELPERS
# ============================================================================

def print_setup_guide():
    """Print Docker Compose setup guide."""
    print(DOCKER_COMPOSE_SETUP)


def print_cli_reference():
    """Print CLI command reference."""
    print(CLI_COMMANDS)


def get_config():
    """Get appropriate config based on environment."""
    env = os.getenv("ENVIRONMENT", "development")
    
    if env == "production":
        return ProductionConfig
    else:
        return DevelopmentConfig


if __name__ == "__main__":
    import sys
    
    if len(sys.argv) > 1:
        if sys.argv[1] == "setup":
            print_setup_guide()
        elif sys.argv[1] == "cli":
            print_cli_reference()
        elif sys.argv[1] == "k8s":
            print(KUBERNETES_MANIFESTS)
    else:
        print("Usage:")
        print("  python littlehorse/config.py setup    # Show Docker Compose setup")
        print("  python littlehorse/config.py cli      # Show CLI commands")
        print("  python littlehorse/config.py k8s      # Show Kubernetes manifests")
