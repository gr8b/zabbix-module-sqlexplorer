DOCKER_IMAGE=noderollup:latest


prepare:
	docker run --rm -it -v $(shell pwd):/app -w /app $(DOCKER_IMAGE) npm install

dev-watch:
	docker run --rm -it --user `id -u`:`id -g` -v $(shell pwd):/app -w /app \
		$(DOCKER_IMAGE) rollup app.js -f iife -o ./public/app.min.js -p @rollup/plugin-node-resolve --watch --compact

docker-init:
	docker build -t $(DOCKER_IMAGE) ./

zip-release:
	zip release.zip actions/* public/* views/* helpers/html/* Module.php manifest.json
