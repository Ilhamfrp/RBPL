<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'SIM Pelayanan Kapal') ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  <style>
    [x-cloak] { display: none !important; }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
    .card { background: white; border-radius: 0.75rem; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
    .btn-primary { background: #1d4ed8; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; font-weight: 500; font-size: 0.875rem; border: none; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; gap: 0.375rem; }
    .btn-primary:hover { background: #1e40af; }
    .btn-outline { background: white; color: #374151; padding: 0.5rem 1rem; border-radius: 0.5rem; font-weight: 500; font-size: 0.875rem; border: 1px solid #d1d5db; cursor: pointer; transition: all 0.15s; display: inline-flex; align-items: center; gap: 0.375rem; }
    .btn-outline:hover { background: #f9fafb; border-color: #9ca3af; }
    .btn-danger { background: #dc2626; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; font-weight: 500; font-size: 0.875rem; border: none; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; gap: 0.375rem; }
    .btn-danger:hover { background: #b91c1c; }
    .btn-success { background: #16a34a; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; font-weight: 500; font-size: 0.875rem; border: none; cursor: pointer; transition: background 0.15s; display: inline-flex; align-items: center; gap: 0.375rem; }
    .btn-success:hover { background: #15803d; }
    .form-input { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem; outline: none; background: #f9fafb; transition: border-color 0.15s, box-shadow 0.15s; }
    .form-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); background: white; }
    .form-label { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.375rem; }
    .badge { display: inline-flex; align-items: center; padding: 0.125rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; }
    .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 0.5rem; padding: 0.875rem 1rem; color: #166534; display: flex; align-items: flex-start; gap: 0.5rem; }
    .alert-error { background: #fef2f2; border: 1px solid #fecaca; border-radius: 0.5rem; padding: 0.875rem 1rem; color: #991b1b; display: flex; align-items: flex-start; gap: 0.5rem; }
    .alert-warning { background: #fffbeb; border: 1px solid #fde68a; border-radius: 0.5rem; padding: 0.875rem 1rem; color: #92400e; display: flex; align-items: flex-start; gap: 0.5rem; }
  </style>
</head>
<body class="bg-gray-50 text-gray-900">
