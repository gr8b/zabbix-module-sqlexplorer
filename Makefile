DOCKER_IMAGE=noderollup:latest
VERSION_TAG=$(shell git describe --tags --abbrev=0)


prepare:
	docker run --rm -it -v $(shell pwd):/app -w /app $(DOCKER_IMAGE) npm install

dev-watch:
	docker run --rm -it --user `id -u`:`id -g` -v $(shell pwd):/app -w /app \
		$(DOCKER_IMAGE) rollup app.js -f iife -o ./public/app.min.js -p @rollup/plugin-node-resolve --watch --compact

docker-init:
	docker build -t $(DOCKER_IMAGE) ./

tag:
	@echo "Enter new tag: (v1.4, v2.0, vX.X)"
	@read VERSION_TAG && \
	    echo "Creating new tag $$VERSION_TAG" \
		git tag $$VERSION_TAG -a \
		git push origin --tags
	@echo "done"

zip-release:
	@echo "Making module for tag $(VERSION_TAG)"
	rm -rf $(VERSION_TAG).zip
	zip $(VERSION_TAG).zip actions/* public/* views/* helpers/* helpers/html/* Module.php manifest.json
