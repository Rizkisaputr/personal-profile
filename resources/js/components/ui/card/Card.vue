<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { cn } from "@/lib/utils"

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <div
    data-slot="card"
    :class="
      cn(
        'bg-card text-card-foreground flex flex-col gap-6 rounded-2xl border py-6 shadow-[0_1px_2px_rgb(23_23_23/0.025)]',
        props.class,
      )
    "
  >
    <slot />
  </div>
</template>
