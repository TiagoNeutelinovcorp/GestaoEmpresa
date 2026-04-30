<script setup>
const props = defineProps({
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'destructive', 'outline', 'secondary', 'ghost', 'link'].includes(value)
  },
  size: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'sm', 'lg', 'icon'].includes(value)
  }
})

const variantClasses = {
  default: 'bg-neutral-100 text-neutral-900 hover:bg-neutral-300',
  destructive: 'bg-red-700 text-white hover:bg-red-600',
  outline: 'border border-neutral-700 bg-neutral-900 text-neutral-100 hover:bg-neutral-800',
  secondary: 'bg-neutral-800 text-neutral-100 hover:bg-neutral-700',
  ghost: 'text-neutral-100 hover:bg-neutral-800',
  link: 'text-neutral-200 underline-offset-4 hover:underline'
}

const sizeClasses = {
  default: 'h-10 px-4 py-2',
  sm: 'h-9 rounded-md px-3',
  lg: 'h-11 rounded-md px-8',
  icon: 'h-10 w-10'
}
</script>

<template>
  <button :class="[
    'inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-neutral-500 disabled:pointer-events-none disabled:opacity-50',
    variantClasses[variant],
    sizeClasses[size]
  ]">
    <slot />
  </button>
</template>
