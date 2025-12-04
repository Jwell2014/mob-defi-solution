<template>

  <v-container>
    <v-btn
  color="primary"
  class=""
  to="/"
  size="small"
>
  Retour
</v-btn>
     <v-card elevation="2" class="pa-4 mt-6">
        <v-card-title class="text-h6">Statistiques par code analytique</v-card-title>
        <v-card-text>
          <v-form @submit.prevent="fetchStats">
            <v-row dense>
              <v-col cols="12" md="4">
              <v-select
                v-model="statsGroupBy"
                :items="[
                  { title: 'Choisir…', value: null, props: { disabled: true } },
                  { title: 'Aucun', value: 'none' },
                  { title: 'Par jour', value: 'day' },
                  { title: 'Par mois', value: 'month' },
                  { title: 'Par année', value: 'year' }
                ]"
                label="Regrouper par"
                hide-details="auto"
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
                    :model-value="displayStatsFrom"
                    label="Date de début"
                    placeholder="YYYY-MM-DD"
                    prepend-inner-icon="mdi-calendar"
                    readonly
                    hide-details="auto"
                  />
                </template>

                <v-date-picker
                  :model-value="statsFrom"
                  @update:model-value="onSelectFrom"
                  :view-mode="datePickerViewMode"
                  title="Date de début"
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
                      :model-value="displayStatsTo"
                      label="Date de fin"
                      placeholder="YYYY-MM-DD"
                      prepend-inner-icon="mdi-calendar"
                      readonly
                      hide-details="auto"
                    />
                  </template>

                  <v-date-picker
                    :model-value="statsTo"
                    @update:model-value="onSelectTo"
                    :view-mode="datePickerViewMode"
                    title="Date de fin"
                  />
                </v-menu>
              </v-col>
            </v-row>


            <div class="mt-4 d-flex justify-start">
              <v-btn
                type="submit"
                color="black"
                :loading="statsLoading"
                :disabled="!statsGroupBy || statsLoading"
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

          <div v-if="stats && stats.items.length" class="mt-12">
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
                  :style="{backgroundColor: getRowBackgroundColor(item.analyticCode)}"
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
            <h3 class="text-subtitle-1 mt-12 mb-5">Graphique des distances par code analytique</h3>
            <div style="height: 300px;">
              <Bar
                v-if="statsChartData"
                :data="statsChartData"
                :options="statsChartOptions"
              />
            </div>
          </div>

          <!-- Légende -->
          <div
            v-if="statsLegendItems.length"
            class="mt-12 d-flex flex-wrap align-center"
          >
            <h6  class="mr-3">Légende :</h6>

            <div
              v-for="item in statsLegendItems"
              :key="item.code"
              class="d-flex align-center mr-4 mb-2"
            >
              <span
                class="legend-dot mr-2"
                :style="{ backgroundColor: item.color }"
              ></span>
              <span class="text-caption">{{ item.code }}</span>
            </div>
          </div>


          <p v-else-if="stats && !stats.items.length" class="mt-4">
            Aucune statistique disponible pour les filtres choisis.
          </p>
        </v-card-text>
      </v-card>
  </v-container>
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

const stats = ref<AnalyticDistanceResponse | null>(null);
const statsError = ref<ErrorResponse | null>(null);
const statsLoading = ref(false);

type GroupBy = "none" | "day" | "month" | "year";

const statsGroupBy = ref<GroupBy | null>(null);

const datePickerViewMode = computed(() => {
  if (statsGroupBy.value === "month") {
    return "months"; // vue par mois
  }
  if (statsGroupBy.value === "year") {
    return "year"; // vue par années
  }
  // 'none' ou 'day' -> vue normale par jours
  return "month";
});

// toujours au format YYYY-MM-DD pour l'API
const statsFrom = ref<string | null>(null); 
const statsTo = ref<string | null>(null);
const statsFromMenu = ref(false);
const statsToMenu = ref(false);

const normalizePickedDate = (
  raw: string | Date,
  side: "from" | "to"
): string => {
  const date = raw instanceof Date ? raw : new Date(raw);

  if (Number.isNaN(date.getTime())) {
    // fallback au cas où, tu peux adapter
    return formatDateYMD(raw) ?? "";
  }

  const year = date.getFullYear();
  const monthIndex = date.getMonth(); // 0-11
  const month = String(monthIndex + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");

  if (statsGroupBy.value === "month") {
    if (side === "from") {
      // début du mois
      return `${year}-${month}-01`;
    } else {
      // fin du mois (0ème jour du mois suivant = dernier jour du mois courant)
      const lastDay = new Date(year, monthIndex + 1, 0).getDate();
      return `${year}-${month}-${String(lastDay).padStart(2, "0")}`;
    }
  }

  if (statsGroupBy.value === "year") {
    if (side === "from") {
      return `${year}-01-01`;
    } else {
      return `${year}-12-31`;
    }
  }

  // mode "jour" ou "none" -> on garde la date telle quelle
  return `${year}-${month}-${day}`;
};

const onSelectFrom = (value: string | null) => {
  if (!value) {
    statsFrom.value = null;
    statsFromMenu.value = false;
    return;
  }

  statsFrom.value = normalizePickedDate(value, "from");
  statsFromMenu.value = false;
};

const onSelectTo = (value: string | null) => {
  if (!value) {
    statsTo.value = null;
    statsToMenu.value = false;
    return;
  }

  statsTo.value = normalizePickedDate(value, "to");
  statsToMenu.value = false;
};

const displayStatsFrom = computed(() => {
  if (!statsFrom.value) return "";

  const date = new Date(statsFrom.value);
  if (Number.isNaN(date.getTime())) {
    return statsFrom.value;
  }

  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");

  if (statsGroupBy.value === "year") {
    return String(year);
  }

  if (statsGroupBy.value === "month") {
    return `${year}-${month}`;
  }

  return `${year}-${month}-${day}`;
});

const displayStatsTo = computed(() => {
  if (!statsTo.value) return "";

  const date = new Date(statsTo.value);
  if (Number.isNaN(date.getTime())) {
    return statsTo.value;
  }

  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");

  if (statsGroupBy.value === "year") {
    return String(year);
  }

  if (statsGroupBy.value === "month") {
    return `${year}-${month}`;
  }

  return `${year}-${month}-${day}`;
});



const analyticCodeColorMap = new Map<string, string>();

const analyticColorPalette:string[] = [
  "#1f77b4", // bleu
  "#ff7f0e", // orange
  "#2ca02c", // vert
  "#d62728", // rouge
  "#9467bd", // violet
  "#8c564b", // marron
  "#e377c2", // rose
  "#7f7f7f", // gris
  "#bcbd22", // jaune olive
  "#17becf", // turquoise
];

const getColorForAnalyticCode = (code: string): string => {
  if (analyticCodeColorMap.has(code)) {
    return analyticCodeColorMap.get(code)!;
  }

  const paletteSize = analyticColorPalette.length;
  const index = paletteSize ? analyticCodeColorMap.size % paletteSize : 0;
  const color = analyticColorPalette[index] ?? "#1f77b4";

  analyticCodeColorMap.set(code, color);
  return color;
};

// On fait une version "fond de ligne" avec transparence
const getRowBackgroundColor = (code: string): string => {
  const base = getColorForAnalyticCode(code);

  // si c'est un hex du type #rrggbb, on lui ajoute une alpha faible (#rrggbb22)
  if (base.startsWith("#") && base.length === 7) {
    return `${base}22`; // ~13% d'opacité
  }

  return base;
};


const formatDateYMD = (
  value: string | Date | null | undefined
): string | null => {
  if (!value) {
    return null;
  }

  if (value instanceof Date) {
    const year = value.getFullYear();
    const month = String(value.getMonth() + 1).padStart(2, "0");
    const day = String(value.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
  }

  if (typeof value === "string") {
    // Si déjà au bon format
    if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
      return value;
    }
  }

  const date = new Date(value as string | Date);
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

const fetchStats = async () => {
  statsLoading.value = true;
  stats.value = null;
  statsError.value = null;

  try {
    if (!statsGroupBy.value) {
      // sécurité : pas de groupBy -> on ne fait rien
      statsLoading.value = false;
      return;
    }

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

  const labels: (string | string[])[] = [];
  const data: number[] = [];
  const backgroundColors: string[] = [];
  const borderColors: string[] = [];

  for (const item of stats.value.items) {
    const code = item.analyticCode ?? "UNKNOWN";

    //Label multi-ligne : code en haut, groupe/date en bas
    let label: string | string[] = code;

    if (stats.value.groupBy !== "none" && item.group) {
      // ex : ["ANA-123", "2025-12"]
      label = [code, item.group];
    }

    labels.push(label);
    data.push(item.totalDistanceKm);

    const baseColor = getColorForAnalyticCode(code);
    // un peu transparent pour le remplissage
    backgroundColors.push(`${baseColor}99`); 
    // couleur pleine pour la bordure
    borderColors.push(baseColor);            
  }

  return {
    labels,
    datasets: [
      {
        label: "Distance totale (km)",
        data,
        backgroundColor: backgroundColors,
        borderColor: borderColors,
        borderWidth: 1,
      },
    ],
  };
});

const statsLegendItems = computed(() => {
  if (!stats.value || !stats.value.items.length) {
    return [];
  }

  const seen = new Set<string>();
  const items: { code: string; color: string }[] = [];

  for (const item of stats.value.items) {
    const code = item.analyticCode ?? "UNKNOWN";
    if (seen.has(code)) continue;

    seen.add(code);
    items.push({
      code,
      color: getColorForAnalyticCode(code),
    });
  }

  return items;
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


</script>

<style scoped>
.app {
  max-width: 800px;
  margin: 2rem auto;
  padding: 1.5rem;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI",
    sans-serif;
}

.legend-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  display: inline-block;
}

</style>
