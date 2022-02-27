# DC homepage

## Running the project
Download docker.

### Dev
```docker-compose up```

### Production
Change DEPLOYMENT_URL in `docker-compose.prod.yml` to the deployment url.
Update the `nginx.conf` files for the url handling.

```docker-compose -f docker-compose.prod.yml up -d --build```