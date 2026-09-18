<template>
  <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm p-5 sm:p-6 flex flex-col h-full">
    <!-- Header -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
      <div class="flex items-center gap-3">
        <div 
          :class="[
            'w-10 h-10 rounded-xl flex items-center justify-center border',
            colorType === 'red' ? 'bg-rose-50 text-rose-600 border-rose-200/80' : 
            colorType === 'blue' ? 'bg-sky-50 text-sky-600 border-sky-200/80' : 
            'bg-emerald-50 text-emerald-600 border-emerald-200/80'
          ]"
        >
          <TrendingUp v-if="colorType === 'red'" class="w-5 h-5" />
          <BarChart3 v-else-if="colorType === 'blue'" class="w-5 h-5" />
          <Activity v-else class="w-5 h-5" />
        </div>
        <div>
          <h3 class="font-bold text-slate-800 text-sm sm:text-base leading-tight">
            {{ title }}
          </h3>
          <p class="text-xs text-slate-400 mt-0.5">{{ subtitle }}</p>
        </div>
      </div>
      <div class="text-right">
        <span class="text-[11px] px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg font-bold border border-slate-200">
          Tahun {{ currentYear }}
        </span>
      </div>
    </div>

    <!-- Chart Body (Interactive Bar Chart) -->
    <div class="flex-1 flex flex-col justify-end min-h-[220px]">
      <!-- Bars container -->
      <div class="flex items-end justify-between gap-1.5 sm:gap-2.5 h-48 pt-4 px-1">
        <div 
          v-for="(item, index) in data" 
          :key="index"
          class="flex-1 flex flex-col items-center h-full justify-end group relative"
        >
          <!-- Tooltip on hover -->
          <div class="absolute -top-10 bg-slate-900 text-white text-[11px] font-mono font-bold px-2.5 py-1 rounded-lg shadow-xl pointer-events-none opacity-0 group-hover:opacity-100 transition-all duration-150 z-20 whitespace-nowrap transform group-hover:-translate-y-1">
            <span class="text-slate-400 font-sans mr-1">{{ item.label }}:</span>
            <span>{{ formatTooltip(item.value) }}</span>
          </div>

          <!-- Bar -->
          <div 
            :style="{ height: `${getPercentage(item.value)}%` }"
            class="w-full max-w-[28px] rounded-t-lg transition-all duration-300 group-hover:brightness-110 cursor-pointer" 
            :class="barColorClass(item)"
          ></div>
        </div>
      </div>

      <!-- Month Labels -->
      <div class="flex items-center justify-between border-t border-slate-100 pt-3 px-1 mt-2 text-[10px] text-slate-400 font-bold uppercase tracking-wider">
        <span 
          v-for="(item, index) in data" 
          :key="index" 
          class="flex-1 text-center truncate group-hover:text-slate-800"
          :class="{ 'text-emerald-700 font-black': item.highlight }"
        >
          {{ item.label }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { BarChart3, TrendingUp, Activity } from 'lucide-vue-next';

interface ChartItem {
  label: string;
  value: number;
  highlight?: boolean;
}

const props = withDefaults(defineProps<{
  title: string;
  subtitle?: string;
  data: ChartItem[];
  colorType?: 'red' | 'emerald' | 'blue';
  isCurrency?: boolean;
}>(), {
  colorType: 'emerald',
  isCurrency: false
});

const currentYear = new Date().getFullYear();

const maxValue = computed(() => {
  const max = Math.max(...props.data.map(d => d.value), 1);
  return max * 1.15;
});

const getPercentage = (val: number) => {
  if (!val || val <= 0) return 3; // minimal baseline tick
  return Math.min(100, Math.max(8, Math.round((val / maxValue.value) * 100)));
};

const barColorClass = (item: ChartItem) => {
  if (item.value === 0) {
    return 'bg-slate-200/90 rounded-t-sm';
  }
  if (props.colorType === 'blue') {
    return item.highlight 
      ? 'bg-gradient-to-t from-sky-600 to-sky-400 ring-2 ring-sky-400/50 shadow-sm shadow-sky-200' 
      : 'bg-gradient-to-t from-sky-600 to-sky-400';
  }
  if (props.colorType === 'red') {
    return item.highlight
      ? 'bg-gradient-to-t from-rose-600 to-rose-400 ring-2 ring-rose-400/50 shadow-sm shadow-rose-200'
      : 'bg-gradient-to-t from-rose-600 to-rose-400';
  }
  return item.highlight
    ? 'bg-gradient-to-t from-emerald-600 to-emerald-400 ring-2 ring-emerald-400/50 shadow-sm shadow-emerald-200'
    : 'bg-gradient-to-t from-emerald-600 to-emerald-400';
};

const formatTooltip = (val: number) => {
  if (props.isCurrency) {
    return 'Rp ' + Number(val).toLocaleString('id-ID');
  }
  return Number(val).toLocaleString('id-ID') + ' Kendaraan';
};
</script>
