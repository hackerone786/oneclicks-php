# IconScout Proxy - Docker Setup

This Docker setup allows you to run the IconScout proxy application in a containerized environment using **environment variables** for configuration.

## Prerequisites

- Docker
- Docker Compose

## Quick Start

1. **Navigate to the project directory**
   ```bash
   cd /path/to/iconscout
   ```

2. **Set environment variables**
   You can set environment variables in several ways:

   **Option A: Using shell export (recommended for development)**
   ```bash
   export API_URL="https://your-actual-api-url.com"
   export API_KEY="your-actual-api-key"
   ```

   **Option B: Create a .env file (optional, for docker-compose)**
   ```bash
   cp env.example .env
   # Edit .env with your actual values
   ```

   **Option C: Pass directly to docker-compose**
   ```bash
   API_URL="https://your-api-url.com" API_KEY="your-api-key" docker-compose up -d --build
   ```

3. **Build and run with Docker Compose**
   ```bash
   docker-compose up -d --build
   ```

4. **Access the application**
   Open your browser and go to: `http://localhost:8080`

## Environment Variables

### Required Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `API_URL` | The API endpoint URL | `https://your-api-url.com` |
| `API_KEY` | Your API authentication key | `your-api-key-here` |
| `MODE` | Application mode (production/development) | `production` |
| `APP_NAME` | Application name shown in limit pages | `IconScout Proxy` |

### Optional Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `CACHE_EXPIRY` | Cache expiration time in seconds | `3600` |
| `DEBUG` | Enable debug mode | `false` |

## Manual Docker Commands

If you prefer to use Docker directly without docker-compose:

### Build the image
```bash
docker build -t iconscout-proxy .
```

### Run the container
```bash
docker run -d \
  --name iconscout-app \
  -p 8080:80 \
  -v $(pwd)/cache:/var/www/html/cache \
  -v $(pwd)/media:/var/www/html/media \
  -e API_URL="https://your-api-url.com" \
  -e API_KEY="your-api-key" \
  -e CACHE_EXPIRY="3600" \
  -e DEBUG="false" \
  iconscout-proxy
```

## Configuration Methods

### 1. Environment Variables (Recommended)
Set environment variables in your system or CI/CD pipeline:
```bash
export API_URL="https://your-api-url.com"
export API_KEY="your-api-key"
export CACHE_EXPIRY="7200"
export DEBUG="true"
```

### 2. Docker Compose with Environment
Update your `docker-compose.yml` environment section:
```yaml
environment:
  - API_URL=https://your-api-url.com
  - API_KEY=your-api-key
  - CACHE_EXPIRY=3600
  - DEBUG=false
```

### 3. .env File (Fallback)
If no environment variables are found, the application will try to read from `.env` file:
```bash
API_URL=https://your-api-url.com
API_KEY=your-api-key
CACHE_EXPIRY=3600
DEBUG=false
```

### 4. Production with Docker Secrets
For production deployments, use Docker secrets:
```yaml
secrets:
  api_key:
    external: true
services:
  iconscout-proxy:
    secrets:
      - api_key
    environment:
      - API_KEY_FILE=/run/secrets/api_key
```

## Volumes

- `./cache:/var/www/html/cache` - Persistent cache storage
- `./media:/var/www/html/media` - Media files storage

## Ports

- Container port `80` is mapped to host port `8080`
- Access the application at `http://localhost:8080`

## Directory Structure

```
iconscout/
├── Dockerfile              # Docker build configuration
├── docker-compose.yml      # Docker Compose configuration
├── apache-config.conf       # Apache virtual host configuration
├── .dockerignore           # Files to exclude from Docker build
├── env.example             # Example environment file (optional)
├── .env                    # Environment variables (optional fallback)
├── index.php               # Main application entry point
├── functions.cfg.php       # Application functions
├── env.php                 # Environment loader (supports both methods)
├── .htaccess              # Apache rewrite rules
├── cache/                 # Cache directory (auto-created)
├── media/                 # Media storage directory
└── README-Docker.md       # This file
```

## Deployment Examples

### Development
```bash
# Quick development setup
export API_URL="https://dev-api.example.com"
export API_KEY="dev-key-123"
export DEBUG="true"
docker-compose up -d --build
```

### Staging
```bash
# Using .env file for staging
echo "API_URL=https://staging-api.example.com" > .env
echo "API_KEY=staging-key-456" >> .env
echo "DEBUG=false" >> .env
docker-compose up -d --build
```

### Production with Kubernetes
```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: iconscout-proxy
spec:
  template:
    spec:
      containers:
      - name: iconscout-proxy
        image: iconscout-proxy:latest
        env:
        - name: API_URL
          valueFrom:
            secretKeyRef:
              name: iconscout-secrets
              key: api-url
        - name: API_KEY
          valueFrom:
            secretKeyRef:
              name: iconscout-secrets
              key: api-key
```

## Useful Commands

### View logs
```bash
docker-compose logs -f iconscout-proxy
```

### Stop the application
```bash
docker-compose down
```

### Rebuild and restart
```bash
docker-compose down
docker-compose up -d --build
```

### Access container shell
```bash
docker exec -it iconscout-app bash
```

### Check environment variables inside container
```bash
docker exec iconscout-app env | grep -E "API_|CACHE_|DEBUG"
```

### Clear cache
```bash
# Clear cache from host
sudo rm -rf cache/*

# Or from inside container
docker exec iconscout-app rm -rf /var/www/html/cache/*
```

## Troubleshooting

### Environment Variable Issues
1. Check if variables are set:
   ```bash
   echo $API_URL
   echo $API_KEY
   ```

2. Verify variables inside container:
   ```bash
   docker exec iconscout-app printenv | grep API
   ```

3. Check application logs for environment loading:
   ```bash
   docker-compose logs iconscout-proxy | grep -i env
   ```

### API Connection Issues
1. Verify your environment variables contain correct API_URL and API_KEY
2. Check container logs: `docker-compose logs iconscout-proxy`
3. Test API connectivity from inside container:
   ```bash
   docker exec iconscout-app curl -H "Authorization: $API_KEY" "$API_URL/test"
   ```

### Permission Issues
If you encounter permission issues with cache directory:
```bash
sudo chown -R 33:33 cache/  # 33 is www-data user ID
sudo chmod -R 777 cache/
```

### Port Already in Use
If port 8080 is already in use, change it in `docker-compose.yml`:
```yaml
ports:
  - "8081:80"  # Use port 8081 instead
```

## Security Best Practices

- **Never hardcode sensitive values** in Dockerfiles or docker-compose.yml
- **Use environment variables** for all configuration
- **Use Docker secrets** for production deployments
- **Rotate API keys** regularly
- **Use least privilege** for container permissions
- **Enable logging** for security monitoring

## Migration from .env File

If you're migrating from the .env file approach:

1. **Export current .env variables**:
   ```bash
   set -a; source .env; set +a
   ```

2. **Verify variables are loaded**:
   ```bash
   env | grep -E "API_|CACHE_"
   ```

3. **Remove .env volume** from docker-compose.yml (already done)

4. **Restart containers**:
   ```bash
   docker-compose down
   docker-compose up -d --build
   ```

The application will automatically prefer environment variables over .env file content! 