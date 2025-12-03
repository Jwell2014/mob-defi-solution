<!-- src/App.vue -->
<template>
  <v-app>
    <!-- Navbar -->
    <v-app-bar app flat color="primary" dark>
      <v-container class="d-flex align-center justify-space-between">
        <div class="d-flex align-center">
          <v-img
            :src="mobLogo"
            alt="MOB"
            width="70"
            height="70"
            cover
            class="mr-3"
          />
        </div>

        <div>
          <v-btn
            variant="text"
            to="/"
            class="mx-2"
          >
            Accueil
          </v-btn>
          <v-btn
            variant="text"
            to="/calcul"
            class="mx-2"
          >
            Calcul de trajet
          </v-btn>
          <v-btn
            variant="text"
            to="/stats"
            class="mx-2"
          >
            Statistiques
          </v-btn>
        </div>
      </v-container>
    </v-app-bar>

    <!-- Contenu principal -->
    <v-main>
      <router-view />
    </v-main>

    <!-- Footer -->
    <v-footer app padless>
      <v-container class="py-3 d-flex justify-space-between align-center text-caption">
        <span>Défi full-stack MOB – SOTOS Coraline</span>
        <a href='https://sotos-coraline-cv.vercel.app/'>CV en ligne</a>
        <span>Construite avec Symfony, Vue, Vuetify & Docker</span>
      </v-container>
    </v-footer>
  </v-app>
</template>

<script setup lang="ts">
import mobLogo from "../src/assets/mob.png";
</script>






