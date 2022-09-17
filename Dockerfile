FROM node:latest

RUN npm install -g npm
RUN npm install -g rollup
RUN npm install -g @rollup/plugin-node-resolve