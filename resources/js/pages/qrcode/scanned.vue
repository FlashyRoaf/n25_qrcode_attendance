<template>
    <h1>Scanned</h1>
    <p>Divisi: {{ props.division }}</p>
    <p>Shift: {{ props.shift }}</p>
</template>

<script setup lang="ts">
interface Props {
    division: string;
    shift: string;
}

const props = defineProps<Props>();
</script>