# Claude Instructions

## Commit Messages

- Single line only
- No Claude attribution or co-author tags
- Keep it simple and descriptive

## Build & Deploy

```bash
# Build plugin
wordsmith build

# Deploy to local WordPress (manager container)
unzip -o build/roleplay-*.zip -d /tmp/roleplay-extract && \
docker cp /tmp/roleplay-extract/roleplay watchtower_manager_site:/var/www/html/wp-content/plugins/ && \
rm -rf /tmp/roleplay-extract && \
docker exec watchtower_manager_site service apache2 reload
```

## Versioning

Version comes from git tags in format `vX.X.X`. To release:

```bash
git tag v0.0.2
git push origin v0.0.2
wordsmith build
```
