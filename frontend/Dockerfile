# Étape 1 : build Vite
FROM node:22.12-alpine AS build

WORKDIR /app

COPY package*.json ./
RUN npm install

COPY . .
RUN npm run build

# Étape 2 : servir les fichiers statiques avec Nginx
FROM nginx:alpine

# Copier le build dans le dossier statique de Nginx
COPY --from=build /app/dist /usr/share/nginx/html

# Configurer le reverse-proxy pour le backend Symfony
COPY nginx.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
