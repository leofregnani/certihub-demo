<script setup>
import CrudPage from '../components/CrudPage.vue'
import { fmtDate } from '../format'

const columns = [
  { key: 'name', label: 'Course / event' },
  { key: 'workload_hours', label: 'Hours' },
  { key: 'starts_on', label: 'Starts', format: fmtDate },
  { key: 'ends_on', label: 'Ends', format: fmtDate },
]

const fields = [
  { key: 'name', label: 'Course / event name', required: true },
  { key: 'workload_hours', label: 'Workload (hours)', type: 'number', required: true },
  { key: 'starts_on', label: 'Start date', type: 'date' },
  { key: 'ends_on', label: 'End date', type: 'date' },
  { key: 'description', label: 'Description', type: 'textarea' },
]
</script>

<template>
  <CrudPage title="Courses & events" noun="course" url="/courses" :columns="columns" :fields="fields" />
</template>
