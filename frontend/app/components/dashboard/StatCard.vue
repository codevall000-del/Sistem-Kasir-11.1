<template>
  <div 
    class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between group relative overflow-hidden"
  >
    <!-- Top Row: Icon Container & Optional Trend/Badge -->
    <div class="flex items-center justify-between mb-3">
      <div 
        :class="[
          'w-10 h-10 rounded-xl flex items-center justify-center transition-transform group-hover:scale-105 border',
          iconContainerClasses
        ]"
      >
        <Car v-if="iconType === 'car'" class="w-5 h-5" />
        <Users v-else-if="iconType === 'user'" class="w-5 h-5" />
        <Wallet v-else-if="iconType === 'wallet'" class="w-5 h-5" />
        <Clock v-else-if="iconType === 'clock'" class="w-5 h-5" />
        <Receipt v-else-if="iconType === 'receipt'" class="w-5 h-5" />
        <ShieldCheck v-else-if="iconType === 'shield'" class="w-5 h-5" />
        <ParkingSquare v-else class="w-5 h-5" />
      </div>

      <span 
        v-if="trend || badge"
        :class="[
          'text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider border',
          badgeClasses
        ]"
      >
        {{ trend || badge }}
      </span>
    </div>

    <!-- Value and Metric Details -->
    <div>
      <div class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight font-mono leading-none mb-1">
        {{ value }}
      </div>
      <div class="text-xs font-bold text-slate-700 tracking-tight">
        {{ title }}
      </div>
    </div>

    <!-- Bottom Subtitle -->
    <div v-if="subtitle || link" class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
      <span class="truncate">{{ subtitle }}</span>
      <span v-if="link" class="text-emerald-600 font-bold group-hover:translate-x-0.5 transition-transform">
        Rincian
      </span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { 
  Car, 
  Users, 
  Wallet, 
  Clock, 
  Receipt, 
  ParkingSquare,
  ShieldCheck 
} from 'lucide-vue-next';

interface Props {
  title: string;
  value: string | number;
  color?: 'green' | 'orange' | 'blue' | 'purple' | 'red';
  iconType?: 'car' | 'user' | 'wallet' | 'clock' | 'receipt' | 'parking' | 'shield';
  subtitle?: string;
  trend?: string;
  badge?: string;
  link?: string;
}

const props = withDefaults(defineProps<Props>(), {
  color: 'green',
  iconType: 'car'
});

const iconContainerClasses = computed(() => {
  switch (props.color) {
    case 'orange':
      return 'bg-amber-50 text-amber-600 border-amber-200/80';
    case 'blue':
      return 'bg-sky-50 text-sky-600 border-sky-200/80';
    case 'purple':
      return 'bg-purple-50 text-purple-600 border-purple-200/80';
    case 'red':
      return 'bg-rose-50 text-rose-600 border-rose-200/80';
    case 'green':
    default:
      return 'bg-emerald-50 text-emerald-700 border-emerald-200/80';
  }
});

const badgeClasses = computed(() => {
  switch (props.color) {
    case 'orange':
      return 'bg-amber-50 text-amber-700 border-amber-200';
    case 'blue':
      return 'bg-sky-50 text-sky-700 border-sky-200';
    case 'purple':
      return 'bg-purple-50 text-purple-700 border-purple-200';
    case 'red':
      return 'bg-rose-50 text-rose-700 border-rose-200';
    case 'green':
    default:
      return 'bg-emerald-50 text-emerald-700 border-emerald-200';
  }
});
</script>
