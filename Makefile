DOCKER_IMAGE=noderollup:latest
VERSION_TAG=$(shell git describe --tags --abbrev=0)


prepare:
	docker run --rm -it -v $(shell pwd):/app -w /app $(DOCKER_IMAGE) npm install --no-optional

dev-watch:
	docker run --rm -it --user `id -u`:`id -g` -v $(shell pwd):/app -w /app \
		$(DOCKER_IMAGE) rollup app.js -f iife -o ./public/app.min.js -p @rollup/plugin-node-resolve --watch --compact

# TODO: add https://www.npmjs.com/package/rollup-plugin-uglify
buildjs:
	docker run --rm -it --user `id -u`:`id -g` -v $(shell pwd):/app -w /app \
		$(DOCKER_IMAGE) rollup app.js -f iife -o ./public/app.min.js -p @rollup/plugin-node-resolve --compact

docker-init:
	docker build -t $(DOCKER_IMAGE) ./

tag:
	@echo "Current version $(VERSION_TAG)"
	@echo "Enter new tag: (1.4, 2.0, X.X)"
	@read VERSION_TAG && \
		LAST_TAG=$(VERSION_TAG) && \
		LAST_TAG="$${LAST_TAG#v}" && \
		echo "Updating manifest.json file, replace $$LAST_TAG with $$VERSION_TAG" && \
		sed -i "s/\"version\": \"$$LAST_TAG\"/\"version\": \"$$VERSION_TAG\"/g" manifest.json && \
		git add manifest.json && \
		git commit -m "build: adding new tag v$$VERSION_TAG" && \
		git push && \
		git tag v$$VERSION_TAG && \
		git push origin v$$VERSION_TAG
	@echo "done"

zip-release:
	@echo "Making module for tag $(VERSION_TAG)"
	rm -rf $(VERSION_TAG)-5.0.zip
	rm -rf $(VERSION_TAG)-6.4.zip
	$(MAKE) buildjs
	sed -i "s/\"manifest_version\": 2/\"manifest_version\": 1/g" manifest.json
	zip $(VERSION_TAG)-5.0.zip actions/* public/* views/* helpers/* helpers/html/* Module.php manifest.json
	sed -i "s/\"manifest_version\": 1/\"manifest_version\": 2/g" manifest.json
	zip $(VERSION_TAG)-6.4.zip actions/* public/* views/* helpers/* helpers/html/* Module.php manifest.json

