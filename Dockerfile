FROM node:20-slim AS build

WORKDIR /app
COPY package.json .
COPY pnpm-lock.yaml .
RUN npm i -g pnpm

RUN pnpm i 

COPY . .
RUN pnpm build
RUN pnpm prune --production

CMD ["node", "build"]
