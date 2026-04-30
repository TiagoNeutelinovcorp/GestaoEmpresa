<template>
  <div class="w-full text-neutral-100">
    <!-- Search -->
    <div class="flex items-center py-4">
      <div class="relative w-full max-w-sm">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
          />
        </svg>
        <input
          v-model="globalFilter"
          type="text"
          placeholder="Pesquisar..."
            class="flex h-10 w-full rounded-md border border-neutral-700 bg-neutral-900 px-3 py-2 pl-8 text-sm text-neutral-100 placeholder:text-neutral-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-neutral-500 disabled:cursor-not-allowed disabled:opacity-50"
        />
      </div>
    </div>

    <!-- Table -->
    <div class="rounded-md border border-neutral-800 bg-black">
      <div class="relative w-full overflow-auto">
        <table class="w-full caption-bottom text-sm">
          <thead class="border-b border-neutral-800 bg-neutral-950">
            <tr class="border-b border-neutral-800 transition-colors hover:bg-neutral-900">
              <th
                v-for="col in columns"
                :key="col.accessorKey"
                class="h-12 cursor-pointer px-4 text-left align-middle font-medium text-neutral-300 hover:bg-neutral-900"
                @click="col.sortable && toggleSort(col.accessorKey)"
              >
                <div class="flex items-center gap-1">
                  {{ col.header }}
                  <span v-if="sortColumn === col.accessorKey">
                    <span v-if="sortDirection === 'asc'">↑</span>
                    <span v-else>↓</span>
                  </span>
                </div>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="item in paginatedData"
              :key="item.id"
              class="cursor-pointer border-b border-neutral-800 transition-colors hover:bg-neutral-900"
              @click="$emit('row-click', item)"
            >
              <td
                v-for="col in columns"
                :key="col.accessorKey"
                class="p-4 align-middle"
              >
                <slot :name="col.accessorKey" :item="item">
                  {{ item[col.accessorKey] }}
                </slot>
              </td>
            </tr>
            <tr v-if="filteredData.length === 0">
              <td :colspan="columns.length" class="h-24 text-center">
                Nenhum resultado encontrado.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div class="flex items-center justify-end space-x-2 py-4">
      <div class="flex-1 text-sm text-neutral-300">
        Mostrando {{ startIndex + 1 }}-{{ endIndex }} de {{ filteredData.length }} resultados
      </div>
      <div class="space-x-2">
        <button
          @click="previousPage"
          :disabled="currentPage === 1"
          class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-neutral-700 bg-neutral-900 p-0 text-sm font-medium transition-colors hover:bg-neutral-800 disabled:pointer-events-none disabled:opacity-50"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="15 18 9 12 15 6"></polyline>
          </svg>
        </button>
        <span class="text-sm">
          Página {{ currentPage }} de {{ totalPages }}
        </span>
        <button
          @click="nextPage"
          :disabled="currentPage === totalPages"
          class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-neutral-700 bg-neutral-900 p-0 text-sm font-medium transition-colors hover:bg-neutral-800 disabled:pointer-events-none disabled:opacity-50"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 18 15 12 9 6"></polyline>
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  columns: {
    type: Array,
    required: true
  },
  data: {
    type: Array,
    required: true
  },
  pageSize: {
    type: Number,
    default: 10
  }
})

const emit = defineEmits(['row-click', 'update:sort'])

const globalFilter = ref('')
const currentPage = ref(1)
const sortColumn = ref('')
const sortDirection = ref('asc')

const filteredData = computed(() => {
  let result = props.data

  // Filter
  if (globalFilter.value) {
    const search = globalFilter.value.toLowerCase()
    result = result.filter(item =>
      Object.values(item).some(value =>
        String(value).toLowerCase().includes(search)
      )
    )
  }

  // Sort
  if (sortColumn.value) {
    result = [...result].sort((a, b) => {
      const aVal = a[sortColumn.value]
      const bVal = b[sortColumn.value]
      if (sortDirection.value === 'asc') {
        return aVal > bVal ? 1 : -1
      }
      return aVal < bVal ? 1 : -1
    })
  }

  return result
})

const totalPages = computed(() => Math.ceil(filteredData.value.length / props.pageSize))
const startIndex = computed(() => (currentPage.value - 1) * props.pageSize)
const endIndex = computed(() => Math.min(startIndex.value + props.pageSize, filteredData.value.length))
const paginatedData = computed(() => filteredData.value.slice(startIndex.value, endIndex.value))

const toggleSort = (column) => {
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortColumn.value = column
    sortDirection.value = 'asc'
  }
  emit('update:sort', { column: sortColumn.value, direction: sortDirection.value })
}

const previousPage = () => {
  if (currentPage.value > 1) currentPage.value--
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) currentPage.value++
}
</script>
