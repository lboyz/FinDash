<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
    useImgTag?: boolean;
    src?: string;
    alt?: string;
    width?: number | string;
    height?: number | string;
}

const props = withDefaults(defineProps<Props>(), {
    useImgTag: true,
    src: '/FinDash.png',
    alt: 'FinDash Logo',
    width: 50,
    height: 50,
});
</script>

<template>
    <img
        v-if="useImgTag"
        :src="src"
        :alt="alt"
        :width="width"
        :height="height"
        :class="className"
        v-bind="$attrs"
        style="display: inline-block"
    />
    <svg
        v-else
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 50 50"
        :class="className"
        v-bind="$attrs"
    >
        <image :href="src" width="50" height="50" />
    </svg>
</template>
