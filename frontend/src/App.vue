<template>
  <v-app>
    <v-main>
      <v-container class="d-flex justify-center">
        <v-img max-width="200" alt="MOB" src="/mob.png" />
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
                    @input="analyticCode = analyticCode.toUpperCase()"
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
              <p v-if="formattedRouteCreatedAt">
                <strong>Date :</strong> {{ formattedRouteCreatedAt }}
              </p>
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

      <v-card elevation="2" class="pa-4 mt-6">
        <v-card-title class="text-h6">Statistiques par code analytique</v-card-title>
        <v-card-text>
          <v-form @submit.prevent="fetchStats">
            <v-row dense>
              <v-col cols="12" md="4">
                <v-select
                  v-model="statsGroupBy"
                  :items="[
                    { title: 'Aucun', value: 'none' },
                    { title: 'Par jour', value: 'day' },
                    { title: 'Par mois', value: 'month' },
                    { title: 'Par année', value: 'year' }
                  ]"
                  label="Regrouper par"
                />
              </v-col>

              <v-col cols="12" md="4">
                <v-menu
                  v-model="statsFromMenu"
                  :close-on-content-click="false"
                  transition="scale-transition"
                  location="bottom"
                >
                  <template #activator="{ props }">
                    <v-text-field
                      v-bind="props"
                      v-model="statsFrom"
                      label="Date de début"
                      placeholder="YYYY-MM-DD"
                      prepend-inner-icon="mdi-calendar"
                      readonly
                      hide-details="auto"
                    />
                  </template>

                  <v-date-picker
                    v-model="statsFromPicker"
                    title="Date de début"
                    @update:model-value="val => {
                      statsFrom = formatDateYMD(val); statsFromMenu = false;
                    }"
                  />

                </v-menu>
              </v-col>

              <v-col cols="12" md="4">
                <v-menu
                  v-model="statsToMenu"
                  :close-on-content-click="false"
                  transition="scale-transition"
                  location="bottom"
                >
                  <template #activator="{ props }">
                    <v-text-field
                      v-bind="props"
                      v-model="statsTo"
                      label="Date de fin"
                      placeholder="YYYY-MM-DD"
                      prepend-inner-icon="mdi-calendar"
                      readonly
                      hide-details="auto"
                    />
                  </template>

                  <v-date-picker
                    v-model="statsToPicker"
                    title="Date de fin"
                    @update:model-value="val => { 
                      statsTo = formatDateYMD(val); statsToMenu = false;
                    }"
                  />

                </v-menu>
              </v-col>
            </v-row>

            <div class="mt-4 d-flex justify-start">
              <v-btn
                type="submit"
                color="black"
                outlined
                :loading="statsLoading"
              >
                Mettre à jour les statistiques
              </v-btn>
            </div>
          </v-form>

          <v-alert
            v-if="statsError"
            type="error"
            variant="tonal"
            class="mt-4"
          >
            <div><strong>Code :</strong> {{ statsError.code }}</div>
            <div><strong>Message :</strong> {{ statsError.message }}</div>
            <ul v-if="statsError.details?.length">
              <li v-for="(detail, index) in statsError.details" :key="index">
                {{ detail }}
              </li>
            </ul>
          </v-alert>

          <div v-if="stats && stats.items.length" class="mt-4">
            <p class="text-subtitle-2 mb-2">
              Période :
              <span v-if="stats.from || stats.to">
                {{ formattedStatsPeriodFrom ?? '…' }} → {{ formattedStatsPeriodTo ?? '…' }}
              </span>
              <span v-else>
                Toutes les dates
              </span>
              — regroupement : {{ stats.groupBy }}
            </p>

            <v-table>
              <thead>
                <tr>
                  <th>Code analytique</th>
                  <th>Distance totale (km)</th>
                  <th v-if="stats.groupBy !== 'none'">Groupe</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(item, index) in stats.items"
                  :key="item.analyticCode + '-' + (item.group ?? index)"
                >
                  <td>{{ item.analyticCode }}</td>
                  <td>{{ item.totalDistanceKm }}</td>
                  <td v-if="stats.groupBy !== 'none'">
                    {{ item.group }}
                  </td>
                </tr>
              </tbody>
            </v-table>
          </div>

          <div v-if="stats && stats.items.length" class="mt-6">
            <h3 class="text-subtitle-1 mb-2">Graphique des distances par code analytique</h3>
            <div style="height: 300px;">
              <Bar
                v-if="statsChartData"
                :data="statsChartData"
                :options="statsChartOptions"
              />
            </div>
          </div>


          <p v-else-if="stats && !stats.items.length" class="mt-4">
            Aucune statistique disponible pour les filtres choisis.
          </p>
        </v-card-text>
      </v-card>


      </v-container>
    </v-main>
  </v-app>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import { Bar } from "vue-chartjs";
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
} from "chart.js";

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

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

interface AnalyticDistanceItem {
  analyticCode: string;
  totalDistanceKm: number;
  group?: string;
}

interface AnalyticDistanceResponse {
  from: string | null;
  to: string | null;
  groupBy: "none" | "day" | "month" | "year";
  items: AnalyticDistanceItem[];
}

const fromStationId = ref("MX");
const toStationId = ref("ZW");
const analyticCode = ref("ANA-123");

const route = ref<RouteResponse | null>(null);
const error = ref<ErrorResponse | null>(null);
const isLoading = ref(false);

const stats = ref<AnalyticDistanceResponse | null>(null);
const statsError = ref<ErrorResponse | null>(null);
const statsLoading = ref(false);

const statsGroupBy = ref<"none" | "day" | "month" | "year">("none");

const statsFrom = ref<string | null>(null); // toujours au format YYYY-MM-DD pour l'API
const statsTo = ref<string | null>(null);
const statsFromMenu = ref(false);
const statsToMenu = ref(false);

// valeurs internes pour le date-picker
const statsFromPicker = ref<string | null>(null);
const statsToPicker = ref<string | null>(null);

const formatDateYMD = (value: string | null | undefined): string | null => {
  if (!value) {
    return null;
  }

  // Si déjà au bon format
  if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
    return value;
  }

  const date = new Date(value);
  if (Number.isNaN(date.getTime())) {
    return null;
  }

  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");

  return `${year}-${month}-${day}`;
};


const formattedStatsPeriodFrom = computed(() =>
  formatDateYMD(stats.value?.from)
);
const formattedStatsPeriodTo = computed(() => formatDateYMD(stats.value?.to));
const formattedRouteCreatedAt = computed(() =>
  formatDateYMD(route.value?.createdAt)
);

const fetchStats = async () => {
  statsLoading.value = true;
  stats.value = null;
  statsError.value = null;

  try {
    const params = new URLSearchParams();
    params.set("groupBy", statsGroupBy.value);

    const fromParam = formatDateYMD(statsFrom.value);
    const toParam = formatDateYMD(statsTo.value);

    if (fromParam) {
      params.set("from", fromParam);
    }
    if (toParam) {
      params.set("to", toParam);
    }

    const response = await fetch(`/api/v1/stats/distances?${params.toString()}`, {
      headers: {
        Authorization: "Bearer dev-secret-token",
      },
    });

    const data = await response.json();

    if (!response.ok) {
      statsError.value = {
        code: data.code ?? "UNKNOWN_ERROR",
        message: data.message ?? "Une erreur inconnue est survenue lors de la récupération des statistiques.",
        details: data.details ?? [],
      };
      return;
    }

    stats.value = data as AnalyticDistanceResponse;
  } catch (e) {
    statsError.value = {
      code: "NETWORK_ERROR",
      message: "Impossible de contacter le serveur pour les statistiques.",
      details: [String(e)],
    };
  } finally {
    statsLoading.value = false;
  }
};

const statsChartData = computed(() => {
  if (!stats.value || !stats.value.items.length) {
    return null;
  }

  const labels = stats.value.items.map((item) => {
    if (stats.value?.groupBy && stats.value.groupBy !== "none" && item.group) {
      return `${item.analyticCode} (${item.group})`;
    }
    return item.analyticCode;
  });

  const data = stats.value.items.map((item) => item.totalDistanceKm);

  return {
    labels,
    datasets: [
      {
        label: "Distance totale (km)",
        data,
      },
    ],
  };
});

const statsChartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false,
    },
    title: {
      display: false,
    },
  },
  scales: {
    x: {
      ticks: {
        autoSkip: true,
        maxRotation: 45,
        minRotation: 0,
      },
    },
    y: {
      beginAtZero: true,
    },
  },
}));


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
