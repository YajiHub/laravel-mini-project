@extends('layouts.app')
@section('content')
<div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
  <div class="max-w-4xl mx-auto">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900"><i class="fas fa-cogs mr-2 text-blue-600"></i>System Utilities</h1>
      <p class="text-sm text-gray-500 mt-1">Admin tools — database backup, SQL export, documentation</p>
    </div>

    @if(session('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
      <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
      <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-3"><i class="fas fa-database mr-2 text-blue-500"></i>Database Export (Full)</h2>
        <p class="text-sm text-gray-600 mb-4">Download a complete SQL dump including schema and all data.</p>
        <a href="{{ route('admin.sql-dump') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-sm transition">
          <i class="fas fa-download mr-2"></i>Download SQL Dump
        </a>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-3"><i class="fas fa-sitemap mr-2 text-purple-500"></i>Database Schema Only</h2>
        <p class="text-sm text-gray-600 mb-4">Download database structure only (no data). Useful for documentation.</p>
        <a href="{{ route('admin.schema-dump') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg text-sm transition">
          <i class="fas fa-download mr-2"></i>Download Schema
        </a>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-3"><i class="fas fa-file-pdf mr-2 text-red-500"></i>User Manual (PDF)</h2>
        <p class="text-sm text-gray-600 mb-4">Download the complete user manual with all features documented.</p>
        <a href="{{ route('admin.user-manual') }}" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg text-sm transition">
          <i class="fas fa-download mr-2"></i>Download PDF
        </a>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-3"><i class="fas fa-code mr-2 text-green-500"></i>Technical Documentation</h2>
        <p class="text-sm text-gray-600 mb-4">Download architecture docs, API routes, and technology stack details.</p>
        <a href="{{ route('admin.tech-docs') }}" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg text-sm transition">
          <i class="fas fa-download mr-2"></i>Download PDF
        </a>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-3"><i class="fas fa-shield-alt mr-2 text-yellow-500"></i>Manual Backup</h2>
        <p class="text-sm text-gray-600 mb-4">Create a full database backup now. Automated backups run weekly on Sundays.</p>
        <form method="POST" action="{{ route('backup.run') }}" onsubmit="return confirm('Run database backup now?')">
          @csrf
          <button class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg text-sm transition">
            <i class="fas fa-save mr-2"></i>Backup Now
          </button>
        </form>
      </div>

    </div>
  </div>
</div>
@endsection
