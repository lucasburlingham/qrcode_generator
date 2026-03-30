# Update, Build, and Push Docker Container Skill

## Purpose

This skill is a reusable workflow for updating project files, rebuilding the Docker image, running tests, and pushing the image to Docker Hub.

## Scope

Repository-scoped, works in any repo with a Dockerfile and docker-compose.yml. Ideal for small web apps or static site containers.

## Inputs

- `dockerImageName` (string): local image name (e.g., `qrcode_generator`)
- `dockerHubRepo` (string): remote repo e.g., `username/qrcode_generator`
- `tag` (string, optional): image tag (`latest` default)

## Outputs

- Build result (success/fail)
- Runtime verification (services up, API smoke test)
- Push result
- Updated README entry

## Workflow

1. Check workspace files: `Dockerfile`, `docker-compose.yml`, `README.md`.
2. Update README with commands and automations if needed.
3. Add a quick markdown notice for style: include blank lines before and after header and list blocks in any updated `.md`.
4. Run `docker compose down` to clear old containers.
5. Run `docker compose up --build -d`.
6. Wait briefly and then verify container status with `docker compose ps`.
7. Run smoke checks for exposed endpoints (e.g., `http://localhost:8080`) via local curl/python.
8. If all good, run:
   - `docker build -t $dockerImageName:$tag .`
   - `docker tag $dockerImageName:$tag $dockerHubRepo:$tag`
   - `docker push $dockerHubRepo:$tag`
9. Report success/failure at each step.

## Quality checks

- `docker compose config` passes
- `docker compose ps` shows `Up`
- container logs have no `emerg` or fatal errors
- shortener API returns valid JSON
- redis/services optional toggles validated if present

## Notes

- Avoid pushing untested code.
- Keep destructive commands (`docker compose down`) controlled with confirmation.
- Adapt for private registries with `DOCKER_REGISTRY` env.
