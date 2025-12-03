<!-- src/pages/Home.vue -->
<template>
  <v-container class="py-12">
    <v-row align="center" justify="center">
      <v-col cols="12" md="8">
        <v-card elevation="4" class="pa-8">
          <div class="text-center mb-6">
            <v-img
              :src="mobLogo"
              alt="MOB"
              max-width="120"
              class="mx-auto mb-4"
            />
            <h1 class="text-h4 font-weight-bold mb-2">
              Routage de train & statistiques
            </h1>
            <p class="text-body-1 text-medium-emphasis">
              Calculez le plus court trajet entre deux stations, puis analysez les distances parcourues
              par code analytique grâce à des statistiques détaillées et un graphique dédié.
            </p>
          </div>

          <v-row class="mt-6" align="center" justify="center">
            <v-col cols="12" md="6" class="mb-4">
              <v-card
                elevation="2"
                class="pa-4 h-100 d-flex flex-column justify-space-between"
              >
                <div>
                  <h2 class="text-h6 font-weight-medium mb-2">
                    Calculer un trajet
                  </h2>
                  <p class="text-body-2 text-medium-emphasis mb-4">
                    Saisissez une station de départ, une station d’arrivée et un code analytique.
                    L’application calcule la distance et le chemin le plus court.
                  </p>
                </div>
                <div class="text-right">
                  <v-btn
                    color="primary"
                    to="/calcul"
                    prepend-icon="mdi-train"
                  >
                    Aller au calcul
                  </v-btn>
                </div>
              </v-card>
            </v-col>

            <v-col cols="12" md="6" class="mb-4">
              <v-card
                elevation="2"
                class="pa-4 h-100 d-flex flex-column justify-space-between"
              >
                <div>
                  <h2 class="text-h6 font-weight-medium mb-2">
                    Voir les statistiques
                  </h2>
                  <p class="text-body-2 text-medium-emphasis mb-4">
                    Filtrez par période et par regroupement, consultez les distances agrégées
                    par code analytique et visualisez-les dans un graphique.
                  </p>
                </div>
                <div class="text-right">
                  <v-btn
                    color="secondary"
                    to="/stats"
                    prepend-icon="mdi-chart-bar"
                  >
                    Ouvrir les stats
                  </v-btn>
                </div>
              </v-card>
            </v-col>
          </v-row>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup lang="ts">
import mobLogo from "../assets/mob.png";
</script>
