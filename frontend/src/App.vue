<template>
  <v-app>
    <v-main>
      <v-container class="d-flex justify-center">
        <v-img max-width="200" alt="MOB" src="../public/mob.png"/>
      </v-container>
      <v-container class="py-8" max-width="800">
        <v-card elevation="4" class="pa-6 mb-6">
          <v-card-title class="text-h5 text-center">
            Calcul de trajet train
          </v-card-title>

          <v-card-text>
            <v-form @submit.prevent="onSubmit">
              <v-row dense>
                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="fromStationId"
                    label="Station de départ"
                    placeholder="MX"
                    class="text-uppercase"
                    @input="fromStationId = fromStationId.toUpperCase()"
                    required
                  />
                </v-col>

                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="toStationId"
                    label="Station d’arrivée"
                    placeholder="ZW"
                    class="text-uppercase"
                    @input="toStationId = toStationId.toUpperCase()"
                    required
                  />
                </v-col>

                <v-col cols="12" md="4">
                  <v-text-field
                    v-model="analyticCode"
                    label="Code analytique"
                    placeholder="ANA-123"
                    required
                  />
                </v-col>
              </v-row>

              <div class="mt-4 d-flex justify-start">
                <v-btn
                  type="submit"
                  color="black"
                  prepend-icon="$vuetify"
                  outlined
                  :loading="isLoading"
                >
                  Calculer le trajet
                </v-btn>
              </div>
            </v-form>
          </v-card-text>
        </v-card>

        <v-alert v-if="error" type="error" variant="tonal" class="mb-4">
          <div><strong>Code :</strong> {{ error.code }}</div>
          <div><strong>Message :</strong> {{ error.message }}</div>
          <ul v-if="error.details?.length">
            <li v-for="(detail, index) in error.details" :key="index">
              {{ detail }}
            </li>
          </ul>
        </v-alert>

        <v-card v-if="route" elevation="2" class="pa-4">
          <v-card-title class="text-h6"> Résultat du trajet </v-card-title>
          <v-card-text>
            <div class="d-flex flex-wrap justify-space-around">
              <p><strong>De :</strong> {{ route.fromStationId }}</p>
              <p><strong>À :</strong> {{ route.toStationId }}</p>
              <p><strong>Code analytique :</strong> {{ route.analyticCode }}</p>
              <p><strong>Distance :</strong> {{ route.distanceKm }} km</p>
            </div>
            <p class="mt-2" v-if="route.path.length === 0">
              <strong>Chemin :</strong>
              <span>Aucun chemin trouvé</span>
            </p>
            <div class="mt-4">
              <h3 class="text-subtitle-1 mb-2">Détail du trajet</h3>

              <v-timeline v-if="timelineItems.length" density="compact">
                <v-timeline-item
                  v-for="item in timelineItems"
                  :key="item.stationCode + '-' + item.label"
                  :dot-color="item.color"
                  size="x-small"
                >
                  <div class="mb-2">
                    <div class="font-weight-normal">
                      <strong>{{ item.stationCode }}</strong>
                      <span class="ml-2">{{ item.label }}</span>
                    </div>
                  </div>
                </v-timeline-item>
              </v-timeline>
            </div>

            <div class="mt-4 text-subtitle-2">
              <div>
                <strong>Stations dans le réseau :</strong>
                {{ route.debug_stationCount }}
              </div>

              <div class="mt-1 d-flex align-start" style="column-gap: 16px">
                <p class="mb-0">
                  <strong>Voisins de la station de départ :</strong>
                </p>
                <ul class="mb-0 ml-5">
                  <li
                    v-for="(distance, stationCode) in route.debug_neighborsFrom"
                    :key="stationCode"
                  >
                    {{ fromStationId }} → {{ stationCode }} : {{ distance }} km
                  </li>
                </ul>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-container>
    </v-main>
  </v-app>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";

interface RouteResponse {
  id: string;
  fromStationId: string;
  toStationId: string;
  analyticCode: string;
  distanceKm: number;
  path: string[];
  createdAt: string;
  debug_stationCount?: number;
  debug_neighborsFrom?: Record<string, number>;
}

interface ErrorResponse {
  code: string;
  message: string;
  details?: string[];
}

const fromStationId = ref("MX");
const toStationId = ref("ZW");
const analyticCode = ref("ANA-123");

const route = ref<RouteResponse | null>(null);
const error = ref<ErrorResponse | null>(null);
const isLoading = ref(false);

const timelineItems = computed(() => {
  if (!route.value || !route.value.path || route.value.path.length === 0) {
    return [];
  }

  const path = route.value.path;
  return path.map((stationCode, index) => {
    const isFirst = index === 0;
    const isLast = index === path.length - 1;

    return {
      stationCode,
      label: isFirst ? "Départ" : isLast ? "Arrivée" : `Étape ${index}`,
      color: isFirst ? "green" : isLast ? "red" : "primary",
    };
  });
});

const onSubmit = async () => {
  isLoading.value = true;
  route.value = null;
  error.value = null;

  try {
    const response = await fetch("/api/v1/routes", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Authorization: 'Bearer dev-secret-token',
      },
      body: JSON.stringify({
        fromStationId: fromStationId.value,
        toStationId: toStationId.value,
        analyticCode: analyticCode.value,
      }),
    });

    const data = await response.json();

    if (!response.ok) {
      // On assume que le backend renvoie un schéma Error en cas de problème
      error.value = {
        code: data.code ?? "UNKNOWN_ERROR",
        message: data.message ?? "Une erreur inconnue est survenue.",
        details: data.details ?? [],
      };
      return;
    }

    route.value = data as RouteResponse;
  } catch (e) {
    error.value = {
      code: "NETWORK_ERROR",
      message: "Impossible de contacter le serveur.",
      details: [String(e)],
    };
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
.app {
  max-width: 800px;
  margin: 2rem auto;
  padding: 1.5rem;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI",
    sans-serif;
}
</style>
