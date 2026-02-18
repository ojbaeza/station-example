<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Station Example</title>
    <script>
        (function() {
            const stored = localStorage.getItem('station-theme');
            if (stored === 'dark' || (!stored && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        border: "hsl(var(--border))",
                        input: "hsl(var(--input))",
                        ring: "hsl(var(--ring))",
                        background: "hsl(var(--background))",
                        foreground: "hsl(var(--foreground))",
                        primary: {
                            DEFAULT: "hsl(var(--primary))",
                            foreground: "hsl(var(--primary-foreground))",
                        },
                        secondary: {
                            DEFAULT: "hsl(var(--secondary))",
                            foreground: "hsl(var(--secondary-foreground))",
                        },
                        muted: {
                            DEFAULT: "hsl(var(--muted))",
                            foreground: "hsl(var(--muted-foreground))",
                        },
                        accent: {
                            DEFAULT: "hsl(var(--accent))",
                            foreground: "hsl(var(--accent-foreground))",
                        },
                        destructive: {
                            DEFAULT: "hsl(var(--destructive))",
                            foreground: "hsl(var(--destructive-foreground))",
                        },
                        card: {
                            DEFAULT: "hsl(var(--card))",
                            foreground: "hsl(var(--card-foreground))",
                        },
                    },
                    borderRadius: {
                        lg: "0.5rem",
                        md: "calc(0.5rem - 2px)",
                        sm: "calc(0.5rem - 4px)",
                    },
                }
            }
        }
    </script>
    <style>
        :root {
            --border: 220 13% 86%;
            --input: 220 13% 86%;
            --ring: 224 71.4% 4.1%;
            --background: 0 0% 100%;
            --foreground: 224 71.4% 4.1%;
            --primary: 224 71.4% 4.1%;
            --primary-foreground: 210 20% 98%;
            --secondary: 220 14.3% 93%;
            --secondary-foreground: 220.9 39.3% 11%;
            --muted: 220 14.3% 93%;
            --muted-foreground: 220 8.9% 40%;
            --accent: 220 14.3% 93%;
            --accent-foreground: 220.9 39.3% 11%;
            --destructive: 0 84.2% 60.2%;
            --destructive-foreground: 210 20% 98%;
            --card: 0 0% 100%;
            --card-foreground: 224 71.4% 4.1%;
        }
        .dark {
            --border: 240 3.7% 15.9%;
            --input: 240 3.7% 15.9%;
            --ring: 240 4.9% 83.9%;
            --background: 240 10% 3.9%;
            --foreground: 0 0% 98%;
            --primary: 0 0% 98%;
            --primary-foreground: 240 5.9% 10%;
            --secondary: 240 3.7% 15.9%;
            --secondary-foreground: 0 0% 98%;
            --muted: 240 3.7% 15.9%;
            --muted-foreground: 240 5% 64.9%;
            --accent: 240 3.7% 15.9%;
            --accent-foreground: 0 0% 98%;
            --destructive: 0 62.8% 30.6%;
            --destructive-foreground: 0 0% 98%;
            --card: 240 10% 3.9%;
            --card-foreground: 0 0% 98%;
        }
        body { font-feature-settings: "rlig" 1, "calt" 1; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
</head>
<body class="bg-background text-foreground min-h-screen antialiased">
    <div class="container mx-auto px-4 py-10 max-w-6xl">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="h-7 w-7 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 4h8a4 4 0 014 4v6a4 4 0 01-4 4H8a4 4 0 01-4-4V8a4 4 0 014-4z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 18l-2 4m8-4l2 4M9 11h.01M15 11h.01M8 14h8" />
                    </svg>
                    <div>
                        <h1 class="text-2xl font-semibold tracking-tight">Station Example</h1>
                        <p class="text-sm text-muted-foreground">Test queue drivers, batches, and workflows</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button id="theme-toggle" onclick="toggleTheme()" class="inline-flex items-center justify-center h-9 w-9 rounded-lg border border-border text-muted-foreground hover:bg-secondary hover:text-foreground transition-colors" title="Toggle theme">
                        <svg id="theme-icon-sun" class="h-4 w-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg id="theme-icon-moon" class="h-4 w-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>
                    <a href="/station" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-border text-muted-foreground hover:bg-secondary hover:text-foreground transition-colors text-sm font-medium">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Station Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Driver Status -->
        <div class="rounded-lg border border-border bg-card p-6 mb-6">
            <div class="flex items-center gap-2 mb-4">
                <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <h2 class="text-sm font-semibold">Driver Connectivity</h2>
            </div>
            <div id="driver-status" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="flex border-b border-border mb-6">
            <button onclick="switchTab('jobs')" id="tab-jobs" class="tab-btn flex items-center gap-2 px-6 py-3 text-sm font-medium border-b-2 border-emerald-500 text-emerald-400 -mb-px transition-all">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                Jobs
            </button>
            <button onclick="switchTab('batches')" id="tab-batches" class="tab-btn flex items-center gap-2 px-6 py-3 text-sm font-medium border-b-2 border-transparent text-muted-foreground hover:text-foreground -mb-px transition-all">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                Batches
            </button>
            <button onclick="switchTab('workflows')" id="tab-workflows" class="tab-btn flex items-center gap-2 px-6 py-3 text-sm font-medium border-b-2 border-transparent text-muted-foreground hover:text-foreground -mb-px transition-all">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                </svg>
                Workflows
            </button>
            <button onclick="switchTab('workers')" id="tab-workers" class="tab-btn flex items-center gap-2 px-6 py-3 text-sm font-medium border-b-2 border-transparent text-muted-foreground hover:text-foreground -mb-px transition-all">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Workers
            </button>
            <button onclick="switchTab('howto')" id="tab-howto" class="tab-btn flex items-center gap-2 px-6 py-3 text-sm font-medium border-b-2 border-transparent text-muted-foreground hover:text-foreground -mb-px transition-all">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                How To
            </button>
        </div>

        <!-- Tab Content: Jobs -->
        <div id="content-jobs" class="tab-content active space-y-6">
            <!-- Dispatch Form -->
            <div class="rounded-lg border border-border bg-card p-6">
                <div class="flex items-center gap-2 mb-4">
                    <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <h2 class="text-sm font-semibold">Create Jobs</h2>
                </div>
                <form id="dispatch-form" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="space-y-1.5">
                            <label for="driver" class="text-sm font-medium text-muted-foreground">Connection</label>
                            <select id="driver" name="driver" class="flex h-9 w-full rounded-lg border border-input bg-background px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/50 cursor-pointer">
                                @foreach(\Station\Enums\Driver::cases() as $driver)
                                    <option value="{{ $driver->value }}">{{ $driver->label() }}</option>
                                @endforeach
                                <option value="sync">Sync (Immediate)</option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label for="count" class="text-sm font-medium text-muted-foreground">Number of Jobs</label>
                            <input type="number" id="count" name="count" value="100" min="1" max="100000"
                                   class="flex h-9 w-full rounded-lg border border-input bg-background px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/50 tabular-nums">
                        </div>

                        <div class="space-y-1.5">
                            <label for="queue" class="text-sm font-medium text-muted-foreground">Queue Name</label>
                            <input type="text" id="queue" name="queue" value="default"
                                   class="flex h-9 w-full rounded-lg border border-input bg-background px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/50">
                        </div>

                        <div class="space-y-1.5">
                            <label for="delay" class="text-sm font-medium text-muted-foreground">Delay (seconds)</label>
                            <input type="number" id="delay" name="delay" value="0" min="0" max="3600"
                                   class="flex h-9 w-full rounded-lg border border-input bg-background px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/50 tabular-nums">
                        </div>

                        <div class="space-y-1.5">
                            <label for="fail_percentage" class="text-sm font-medium text-muted-foreground">Fail % (testing)</label>
                            <input type="number" id="fail_percentage" name="fail_percentage" value="0" min="0" max="100"
                                   class="flex h-9 w-full rounded-lg border border-input bg-background px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/50 tabular-nums">
                        </div>

                        <div class="flex items-end">
                            <button type="submit" id="dispatch-btn"
                                    class="inline-flex h-9 w-full items-center justify-center gap-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 px-4 text-sm font-medium text-white transition-colors disabled:pointer-events-none disabled:opacity-50">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                Create Jobs
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Batch History -->
            <div class="rounded-lg border border-border bg-card p-6">
                <div class="flex justify-between items-center mb-4">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h2 class="text-sm font-semibold">Job History</h2>
                    </div>
                    <div class="flex gap-2">
                        <select id="job-history-connection-filter" onchange="loadBatches()" class="h-8 rounded-lg border border-input bg-background px-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/50 cursor-pointer">
                            <option value="">All connections</option>
                            @foreach(\Station\Enums\Driver::cases() as $driver)
                                <option value="{{ $driver->value }}">{{ $driver->label() }}</option>
                            @endforeach
                            <option value="sync">Sync</option>
                        </select>
                        <select id="job-history-status-filter" onchange="loadBatches()" class="h-8 rounded-lg border border-input bg-background px-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/50 cursor-pointer">
                            <option value="">All statuses</option>
                            <option value="pending">Pending</option>
                            <option value="complete">Complete</option>
                            <option value="failed">Failed</option>
                        </select>
                        <button id="clear-batches" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium h-8 px-3 border border-border text-muted-foreground hover:bg-secondary hover:text-red-400 transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Clear All
                        </button>
                    </div>
                </div>
                <div id="batch-history" class="space-y-3">
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <svg class="h-10 w-10 text-muted-foreground/30 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <p class="text-sm text-muted-foreground">No batches yet</p>
                        <p class="text-xs text-muted-foreground/70 mt-1">Dispatch some jobs to get started</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Tab Content: Batches -->
        <div id="content-batches" class="tab-content space-y-6">
            <!-- Station Batch Form -->
            <div class="rounded-lg border border-border bg-card p-6">
                <div class="flex items-center gap-2 mb-4">
                    <svg class="h-4 w-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <h2 class="text-sm font-semibold">Create Batch</h2>
                </div>
                <form id="station-batch-form" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="space-y-1.5">
                            <label for="batch-name" class="text-sm font-medium text-muted-foreground">Batch Name</label>
                            <input type="text" id="batch-name" name="name" placeholder="Test Batch"
                                   class="flex h-9 w-full rounded-lg border border-input bg-background px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/50">
                        </div>

                        <div class="space-y-1.5">
                            <label for="batch-count" class="text-sm font-medium text-muted-foreground">Number of Jobs</label>
                            <input type="number" id="batch-count" name="count" value="10" min="1" max="1000"
                                   class="flex h-9 w-full rounded-lg border border-input bg-background px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/50 tabular-nums">
                        </div>

                        <div class="space-y-1.5">
                            <label for="batch-connection" class="text-sm font-medium text-muted-foreground">Connection</label>
                            <select id="batch-connection" name="connection" class="flex h-9 w-full rounded-lg border border-input bg-background px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/50 cursor-pointer">
                                @foreach(\Station\Enums\Driver::cases() as $driver)
                                    <option value="{{ $driver->value }}">{{ $driver->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label for="batch-queue" class="text-sm font-medium text-muted-foreground">Queue</label>
                            <input type="text" id="batch-queue" name="queue" value="default"
                                   class="flex h-9 w-full rounded-lg border border-input bg-background px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/50">
                        </div>

                        <div class="space-y-1.5">
                            <label for="batch-allowed-failures" class="text-sm font-medium text-muted-foreground">Allowed Failures</label>
                            <input type="number" id="batch-allowed-failures" name="allowed_failures" value="0" min="0"
                                   class="flex h-9 w-full rounded-lg border border-input bg-background px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/50 tabular-nums">
                        </div>

                        <div class="space-y-1.5">
                            <label for="batch-fail-percentage" class="text-sm font-medium text-muted-foreground">Fail % (testing)</label>
                            <input type="number" id="batch-fail-percentage" name="fail_percentage" value="0" min="0" max="100"
                                   class="flex h-9 w-full rounded-lg border border-input bg-background px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/50 tabular-nums">
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" id="station-batch-btn"
                                class="inline-flex h-9 items-center justify-center gap-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 px-4 text-sm font-medium text-white transition-colors disabled:pointer-events-none disabled:opacity-50">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            Create Batch
                        </button>
                    </div>
                </form>
            </div>

            <!-- Batch History List -->
            <div class="rounded-lg border border-border bg-card p-6">
                <div class="flex justify-between items-center mb-4">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h2 class="text-sm font-semibold">Batch History</h2>
                    </div>
                    <div class="flex gap-2">
                        <select id="batch-history-connection-filter" onchange="loadStationBatches(1)" class="h-8 rounded-lg border border-input bg-background px-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/50 cursor-pointer">
                            <option value="">All connections</option>
                            @foreach(\Station\Enums\Driver::cases() as $driver)
                                <option value="{{ $driver->value }}">{{ $driver->label() }}</option>
                            @endforeach
                        </select>
                        <select id="batch-history-status-filter" onchange="loadStationBatches(1)" class="h-8 rounded-lg border border-input bg-background px-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/50 cursor-pointer">
                            <option value="">All statuses</option>
                            <option value="pending">Pending</option>
                            <option value="running">Running</option>
                            <option value="completed">Completed</option>
                            <option value="failed">Failed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <button id="clear-station-batches" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium h-8 px-3 border border-border text-muted-foreground hover:bg-secondary hover:text-red-400 transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Clear All
                        </button>
                    </div>
                </div>
                <div id="station-batches" class="space-y-3">
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <svg class="h-10 w-10 text-muted-foreground/30 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z" />
                        </svg>
                        <p class="text-sm text-muted-foreground">No batches yet</p>
                        <p class="text-xs text-muted-foreground/70 mt-1">Create a batch to get started</p>
                    </div>
                </div>
                <div id="station-batches-pagination" class="mt-4"></div>
            </div>
        </div>

        <!-- Tab Content: Workflows -->
        <div id="content-workflows" class="tab-content space-y-6">
            <!-- Workflow Definitions -->
            <div class="rounded-lg border border-border bg-card p-6">
                <div class="flex justify-between items-center mb-4">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                        </svg>
                        <h2 class="text-sm font-semibold">Workflow Definitions</h2>
                        <span id="def-count" class="text-xs text-muted-foreground"></span>
                    </div>
                    <button id="toggle-def-form" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium h-8 px-3 bg-indigo-600 hover:bg-indigo-500 text-white transition-colors">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        New Definition
                    </button>
                </div>

                <!-- Definition Builder Form (hidden by default) -->
                <div id="def-form-wrapper" class="hidden mb-6 rounded-lg border border-border bg-secondary/30 p-5 space-y-4">
                    <h3 id="def-form-title" class="text-sm font-semibold">Create Workflow Definition</h3>
                    <div id="def-form-error" class="hidden text-sm text-red-500"></div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-muted-foreground">Name</label>
                            <input type="text" id="def-name" placeholder="my-workflow" class="flex h-9 w-full rounded-lg border border-input bg-background px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-muted-foreground">Description</label>
                            <input type="text" id="def-description" placeholder="Optional description" class="flex h-9 w-full rounded-lg border border-input bg-background px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-muted-foreground">Timeout (seconds)</label>
                            <input type="number" id="def-timeout" value="300" min="1" class="flex h-9 w-full rounded-lg border border-input bg-background px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50 tabular-nums">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-sm font-medium text-muted-foreground">Connection</label>
                            <select id="def-connection" class="flex h-9 w-full rounded-lg border border-input bg-background px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                                <option value="">Default</option>
                                @foreach(\Station\Enums\Driver::cases() as $driver)
                                    <option value="{{ $driver->value }}">{{ $driver->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Steps builder -->
                    <div>
                        <label class="text-sm font-medium text-muted-foreground mb-2 block">Steps</label>
                        <div id="def-steps" class="space-y-3"></div>
                        <div class="flex gap-2 mt-3">
                            <button id="add-step-btn" class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-lg border border-border text-muted-foreground hover:bg-secondary transition-colors">+ Add Step</button>
                            <button id="add-parallel-btn" class="inline-flex items-center gap-1 text-xs font-medium px-3 py-1.5 rounded-lg border border-border text-muted-foreground hover:bg-secondary transition-colors">+ Add Parallel Group</button>
                        </div>
                    </div>

                    <!-- Step preview -->
                    <div id="def-preview" class="hidden border border-border rounded-lg p-3 bg-background">
                        <h4 class="text-[10px] font-medium text-muted-foreground uppercase tracking-wider mb-2">Preview</h4>
                        <div id="def-preview-content" class="flex flex-wrap items-center gap-2 text-xs"></div>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button id="cancel-def-btn" class="inline-flex items-center justify-center gap-1 rounded-lg text-sm font-medium h-8 px-3 border border-border text-muted-foreground hover:bg-secondary transition-colors">Cancel</button>
                        <button id="submit-def-btn" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium h-8 px-4 bg-indigo-600 hover:bg-indigo-500 text-white transition-colors disabled:opacity-50">Create Definition</button>
                    </div>
                </div>

                <!-- Definitions list -->
                <div id="workflow-definitions" class="space-y-3">
                    <div class="flex flex-col items-center justify-center py-8 text-center">
                        <svg class="h-8 w-8 text-muted-foreground/30 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" /></svg>
                        <p class="text-sm text-muted-foreground">Loading definitions...</p>
                    </div>
                </div>
                <div id="workflow-definitions-pagination" class="mt-4"></div>
            </div>

            <!-- Workflow History List -->
            <div class="rounded-lg border border-border bg-card p-6">
                <div class="flex justify-between items-center mb-4">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-pink-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h2 class="text-sm font-semibold">Workflow History</h2>
                    </div>
                    <div class="flex gap-2">
                        <select id="workflow-history-connection-filter" onchange="loadStationWorkflows(1)" class="h-8 rounded-lg border border-input bg-background px-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/50 cursor-pointer">
                            <option value="">All connections</option>
                            @foreach(\Station\Enums\Driver::cases() as $driver)
                                <option value="{{ $driver->value }}">{{ $driver->label() }}</option>
                            @endforeach
                        </select>
                        <select id="workflow-history-status-filter" onchange="loadStationWorkflows(1)" class="h-8 rounded-lg border border-input bg-background px-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/50 cursor-pointer">
                            <option value="">All statuses</option>
                            <option value="pending">Pending</option>
                            <option value="running">Running</option>
                            <option value="completed">Completed</option>
                            <option value="failed">Failed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <button id="clear-station-workflows" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium h-8 px-3 border border-border text-muted-foreground hover:bg-secondary hover:text-red-400 transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Clear All
                        </button>
                    </div>
                </div>
                <div id="station-workflows" class="space-y-3">
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <svg class="h-10 w-10 text-muted-foreground/30 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                        </svg>
                        <p class="text-sm text-muted-foreground">No workflows yet</p>
                        <p class="text-xs text-muted-foreground/70 mt-1">Create a workflow to get started</p>
                    </div>
                </div>
                <div id="station-workflows-pagination" class="mt-4"></div>
            </div>
        </div>

        <!-- Tab Content: Workers -->
        <div id="content-workers" class="tab-content space-y-6">
            <!-- Launch Workers -->
            <div class="rounded-lg border border-border bg-card p-5">
                <div class="flex items-center gap-2 mb-4">
                    <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h2 class="text-sm font-semibold">Launch Workers</h2>
                </div>
                <form id="launch-worker-form" onsubmit="launchWorker(event)">
                    <div class="flex flex-wrap lg:flex-nowrap items-end gap-3">
                        <div class="w-full sm:w-auto flex-1 min-w-[140px] space-y-1.5">
                            <label class="text-sm font-medium text-muted-foreground">Connection</label>
                            <select name="connection" class="flex h-9 w-full rounded-lg border border-input bg-background px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/50 cursor-pointer">
                                @foreach(\Station\Enums\Driver::cases() as $driver)
                                    <option value="{{ $driver->value }}">{{ $driver->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="w-full sm:w-auto flex-1 min-w-[100px] space-y-1.5">
                            <label class="text-sm font-medium text-muted-foreground">Queue</label>
                            <input type="text" name="queue" value="default" class="flex h-9 w-full rounded-lg border border-input bg-background px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/50">
                        </div>

                        <div class="w-24 space-y-1.5">
                            <label class="text-sm font-medium text-muted-foreground">Workers</label>
                            <div class="flex h-9 rounded-lg border border-input bg-background overflow-hidden">
                                <button type="button" onclick="adjustWorkers(-1)" class="w-8 flex items-center justify-center text-zinc-400 hover:text-white hover:bg-zinc-700/50 transition-colors">-</button>
                                <input type="number" name="workers" id="workers-input" value="1" min="1" max="10" class="w-8 bg-transparent text-sm text-center focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                <button type="button" onclick="adjustWorkers(1)" class="w-8 flex items-center justify-center text-zinc-400 hover:text-white hover:bg-zinc-700/50 transition-colors">+</button>
                            </div>
                        </div>

                        <button type="submit" class="h-9 px-4 inline-flex items-center justify-center gap-2 rounded-lg text-sm font-medium border border-border text-muted-foreground hover:bg-secondary hover:text-emerald-400 transition-colors">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Launch
                        </button>
                    </div>
                </form>
            </div>

            <!-- Worker Controls -->
            <div class="rounded-lg border border-border bg-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <h2 class="text-sm font-semibold">Worker Controls</h2>
                        </div>
                        <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs translate-y-px">
                            <div class="flex items-center gap-1.5">
                                <span class="inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                <span class="text-muted-foreground">Running</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="inline-flex rounded-full h-2 w-2 bg-orange-500"></span>
                                <span class="text-muted-foreground">Paused</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="inline-flex rounded-full h-2 w-2 bg-zinc-500"></span>
                                <span class="text-muted-foreground">Stopped</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-1.5">
                        <button id="start-all-btn" onclick="startAllWorkers()" class="inline-flex items-center justify-center gap-1 rounded-lg text-xs font-medium h-7 px-2.5 border border-border text-muted-foreground hover:bg-secondary hover:text-emerald-400 transition-colors disabled:opacity-50"><svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>Quick Start All</button>
                        <button id="pause-all-btn" onclick="pauseAllWorkers()" class="inline-flex items-center justify-center gap-1 rounded-lg text-xs font-medium h-7 px-2.5 border border-border text-muted-foreground hover:bg-secondary hover:text-orange-400 transition-colors disabled:opacity-50" disabled><svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>Pause All</button>
                        <button id="stop-all-btn" onclick="stopAllWorkers()" class="inline-flex items-center justify-center gap-1 rounded-lg text-xs font-medium h-7 px-2.5 border border-border text-muted-foreground hover:bg-secondary hover:text-red-400 transition-colors disabled:opacity-50" disabled><svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" /></svg>Stop All</button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3" id="worker-controls">
                    @foreach(\Station\Enums\Driver::cases() as $driver)
                    <div class="rounded-lg border border-border bg-card p-3" data-driver="{{ $driver->value }}">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="relative flex h-2.5 w-2.5 worker-status">
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-zinc-500"></span>
                                </span>
                                <span class="font-semibold text-sm">{{ $driver->label() }}</span>
                            </div>
                            <span class="text-xs text-muted-foreground worker-status-text">Stopped</span>
                        </div>
                        <div class="flex gap-1.5">
                            <button onclick="startWorker('{{ $driver->value }}')" class="worker-start-btn flex-1 inline-flex items-center justify-center gap-1 rounded text-sm font-medium h-7 border border-border text-muted-foreground hover:bg-secondary hover:text-emerald-400 transition-colors disabled:opacity-50"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>Quick Start</button>
                            <button onclick="togglePause('{{ $driver->value }}')" class="queue-pause-btn flex-1 inline-flex items-center justify-center gap-1 rounded text-sm font-medium h-7 border border-border text-muted-foreground hover:bg-secondary hover:text-orange-400 transition-colors disabled:opacity-50" title="Pause/Resume Queue" disabled><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>Pause</button>
                            <button onclick="stopWorker('{{ $driver->value }}')" class="worker-stop-btn flex-1 inline-flex items-center justify-center gap-1 rounded text-sm font-medium h-7 border border-border text-muted-foreground hover:bg-secondary hover:text-red-400 transition-colors disabled:opacity-50" disabled><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" /></svg>Stop</button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Tab Content: How To -->
        <div id="content-howto" class="tab-content space-y-6">
            <div class="rounded-lg border border-border bg-card p-6">
                <div class="flex items-center gap-2 mb-4">
                    <svg class="h-4 w-4 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <h2 class="text-sm font-semibold">Station Workers</h2>
                </div>
                <p class="text-sm text-muted-foreground mb-5">Station workers run as supervised processes with built-in metrics tracking, job recovery, checkpointing, and automatic restart on failures.</p>

                <div class="rounded-lg bg-zinc-950 border border-zinc-800 p-5 font-mono text-sm overflow-x-auto space-y-5">
                    <div class="space-y-4">
                        <div>
                            <span class="text-zinc-500 text-xs uppercase tracking-wide"># Start a worker on a specific connection</span>
                            <pre class="text-emerald-400 mt-1.5">php artisan station:work rabbitmq --queue=default</pre>
                        </div>
                        <div>
                            <span class="text-zinc-500 text-xs uppercase tracking-wide"># Multiple workers with custom options</span>
                            <pre class="text-amber-400 mt-1.5">php artisan station:work redis --queue=high,default --workers=4 --sleep=3 --tries=3</pre>
                        </div>
                        <div>
                            <span class="text-zinc-500 text-xs uppercase tracking-wide"># List available connections</span>
                            <pre class="text-cyan-400 mt-1.5">php artisan station:work --list</pre>
                        </div>
                    </div>
                </div>

                <h3 class="text-sm font-medium text-muted-foreground mt-6 mb-3">All Driver Commands</h3>
                <div class="rounded-lg bg-zinc-950 border border-zinc-800 p-5 font-mono text-sm overflow-x-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach(\Station\Enums\Driver::cases() as $driver)
                        <div>
                            <span class="text-zinc-500 text-xs uppercase tracking-wide"># {{ $driver->label() }}</span>
                            <pre class="text-emerald-400 mt-1.5">php artisan station:work {{ $driver->value }}</pre>
                        </div>
                        @endforeach
                    </div>
                </div>

                <h3 class="text-sm font-medium text-muted-foreground mt-6 mb-3">Worker Features</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="rounded-lg border border-border bg-card p-4">
                        <h4 class="text-sm font-semibold text-emerald-400 mb-1">Supervised Processes</h4>
                        <p class="text-sm text-muted-foreground">Workers are managed by a supervisor that automatically restarts them on failure or unexpected exits.</p>
                    </div>
                    <div class="rounded-lg border border-border bg-card p-4">
                        <h4 class="text-sm font-semibold text-amber-400 mb-1">Metrics &amp; Tracking</h4>
                        <p class="text-sm text-muted-foreground">Built-in metrics collection for throughput, processing time, failure rates, and per-queue statistics.</p>
                    </div>
                    <div class="rounded-lg border border-border bg-card p-4">
                        <h4 class="text-sm font-semibold text-purple-400 mb-1">Job Recovery</h4>
                        <p class="text-sm text-muted-foreground">Stuck job detection with automatic recovery strategies: graceful resume, forced restart, or partial recovery.</p>
                    </div>
                    <div class="rounded-lg border border-border bg-card p-4">
                        <h4 class="text-sm font-semibold text-cyan-400 mb-1">Checkpointing</h4>
                        <p class="text-sm text-muted-foreground">Long-running jobs can save progress checkpoints, allowing them to resume from where they left off after interruptions.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-10 text-center">
            <p class="text-xs text-muted-foreground/50">Station — Queue Management for Laravel</p>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Theme toggle
        function updateThemeIcon() {
            const isDark = document.documentElement.classList.contains('dark');
            document.getElementById('theme-icon-sun').classList.toggle('hidden', !isDark);
            document.getElementById('theme-icon-moon').classList.toggle('hidden', isDark);
        }
        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('station-theme', isDark ? 'dark' : 'light');
            updateThemeIcon();
        }
        updateThemeIcon();

        let batchRefreshInterval = null;
        const workerStatus = {};
        let allJobBatches = [];
        let currentTab = 'jobs';
        let tabIntervals = [];

        // ── Pagination helper ──
        function renderPagination(containerId, pagination, loadFn) {
            const container = document.getElementById(containerId);
            if (!container) return;
            if (!pagination || pagination.last_page <= 1) { container.innerHTML = ''; return; }
            const { current_page, last_page, total } = pagination;
            let pages = [];
            for (let i = 1; i <= last_page; i++) {
                if (i === 1 || i === last_page || (i >= current_page - 1 && i <= current_page + 1)) {
                    pages.push(i);
                } else if (pages[pages.length - 1] !== '...') {
                    pages.push('...');
                }
            }
            const btnBase = 'inline-flex items-center justify-center h-8 min-w-[2rem] px-2 text-xs font-medium rounded-lg border transition-colors';
            const btnActive = 'border-emerald-500 bg-emerald-500/10 text-emerald-400';
            const btnNormal = 'border-border text-muted-foreground hover:bg-secondary hover:text-foreground';
            const btnDisabled = 'border-border text-muted-foreground/40 cursor-not-allowed';
            let html = '<div class="flex items-center justify-between"><span class="text-xs text-muted-foreground">' + total + ' total</span><div class="flex items-center gap-1">';
            html += `<button onclick="${loadFn.name}(${current_page - 1})" class="${btnBase} ${current_page <= 1 ? btnDisabled : btnNormal}" ${current_page <= 1 ? 'disabled' : ''}>&laquo;</button>`;
            pages.forEach(p => {
                if (p === '...') {
                    html += `<span class="px-1 text-xs text-muted-foreground/50">&hellip;</span>`;
                } else {
                    html += `<button onclick="${loadFn.name}(${p})" class="${btnBase} ${p === current_page ? btnActive : btnNormal}">${p}</button>`;
                }
            });
            html += `<button onclick="${loadFn.name}(${current_page + 1})" class="${btnBase} ${current_page >= last_page ? btnDisabled : btnNormal}" ${current_page >= last_page ? 'disabled' : ''}>&raquo;</button>`;
            html += '</div></div>';
            container.innerHTML = html;
        }

        // ── Step flow helpers ──
        let expandedDef = null;

        function computeStepLayers(steps) {
            if (!steps || typeof steps !== 'object') return [];
            const stepEntries = Object.entries(steps);
            if (stepEntries.length === 0) return [];

            const allStepMap = {};
            for (const [name, step] of stepEntries) {
                allStepMap[name] = { name, ...step };
            }

            function resolveRealDeps(deps, visited) {
                visited = visited || new Set();
                const resolved = [];
                for (const dep of (deps || [])) {
                    if (visited.has(dep)) continue;
                    visited.add(dep);
                    const depStep = allStepMap[dep];
                    if (!depStep) continue;
                    if (depStep.virtual) {
                        resolved.push(...resolveRealDeps(depStep.dependencies || [], visited));
                    } else {
                        resolved.push(dep);
                    }
                }
                return resolved;
            }

            const realSteps = stepEntries.filter(([, s]) => !s.virtual).map(([name, s]) => ({ name, ...s }));
            if (realSteps.length === 0) return [];

            const resolvedDeps = {};
            for (const s of realSteps) {
                resolvedDeps[s.name] = resolveRealDeps(s.dependencies || []);
            }

            const depths = {};
            function getDepth(name, visited) {
                if (depths[name] !== undefined) return depths[name];
                visited = visited || new Set();
                if (visited.has(name)) return 0;
                visited.add(name);
                const deps = resolvedDeps[name] || [];
                if (deps.length === 0) { depths[name] = 0; return 0; }
                let maxD = 0;
                for (const dep of deps) {
                    const d = getDepth(dep, new Set(visited));
                    if (d + 1 > maxD) maxD = d + 1;
                }
                depths[name] = maxD;
                return maxD;
            }
            for (const s of realSteps) getDepth(s.name);

            const layerMap = {};
            for (const s of realSteps) {
                const d = depths[s.name] || 0;
                if (!layerMap[d]) layerMap[d] = [];
                layerMap[d].push(s);
            }
            const maxLayer = Math.max(...Object.keys(layerMap).map(Number));
            const result = [];
            for (let i = 0; i <= maxLayer; i++) {
                if (layerMap[i]) result.push(layerMap[i]);
            }
            return result;
        }

        function renderStepFlow(steps) {
            const layers = computeStepLayers(steps);
            if (layers.length === 0) return '<p class="text-xs text-muted-foreground py-2">No steps</p>';
            const arrow = '<div class="flex items-center justify-center self-center px-1"><svg class="h-5 w-8 text-muted-foreground/40" viewBox="0 0 32 20" fill="none"><path d="M0 10h24M20 4l8 6-8 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg></div>';

            function groupLayer(layer) {
                const groups = [];
                const seen = new Set();
                for (const step of layer) {
                    const pg = step.parallel_group || null;
                    if (pg && !seen.has(pg)) {
                        seen.add(pg);
                        groups.push({ group: pg, steps: layer.filter(s => s.parallel_group === pg) });
                    } else if (!pg) {
                        groups.push({ group: null, steps: [step] });
                    }
                }
                return groups;
            }

            let flowHtml = '<div data-flow-content class="flex items-center gap-1 py-2 w-fit" style="transform-origin:top left">';
            layers.forEach((layer, idx) => {
                if (idx > 0) flowHtml += arrow;
                const groups = groupLayer(layer);
                flowHtml += '<div class="flex flex-col gap-1.5">';
                groups.forEach(({ group, steps: groupSteps }) => {
                    if (group && groupSteps.length > 1) {
                        flowHtml += `<div class="rounded-lg border border-indigo-300/40 dark:border-indigo-500/20 bg-indigo-50/30 dark:bg-indigo-500/5 px-2 pb-2">
                            <span class="text-[9px] font-medium text-indigo-600/70 dark:text-indigo-400/70 uppercase tracking-wider">${group}</span>
                            <div class="flex flex-col gap-1 mt-1">`;
                        groupSteps.forEach(step => {
                            flowHtml += `<div class="flex items-center gap-2 rounded-md border border-border bg-card px-3 py-1.5 min-w-[120px]">
                                <span class="h-1.5 w-1.5 rounded-full bg-indigo-500/60 flex-shrink-0"></span>
                                <span class="text-xs font-medium text-foreground truncate">${step.name}</span>
                            </div>`;
                        });
                        flowHtml += '</div></div>';
                    } else {
                        groupSteps.forEach(step => {
                            flowHtml += `<div class="flex items-center gap-2 rounded-md border border-border bg-secondary/50 px-3 py-1.5 min-w-[120px]">
                                <span class="h-1.5 w-1.5 rounded-full bg-indigo-500/60 flex-shrink-0"></span>
                                <span class="text-xs font-medium text-foreground truncate">${step.name}</span>
                            </div>`;
                        });
                    }
                });
                flowHtml += '</div>';
            });
            flowHtml += '</div>';

            return `<div data-flow-wrapper class="relative">
                <div class="absolute top-1 right-1 z-10 flex items-center gap-0.5 rounded border border-border bg-card/80 backdrop-blur-sm px-1 py-0.5">
                    <button type="button" onclick="zoomFlow(this,-0.1)" class="p-0.5 text-muted-foreground hover:text-foreground transition-colors" title="Zoom out"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" /></svg></button>
                    <span data-flow-zoom-label class="text-[10px] text-muted-foreground tabular-nums w-8 text-center select-none">100%</span>
                    <button type="button" onclick="zoomFlow(this,0.1)" class="p-0.5 text-muted-foreground hover:text-foreground transition-colors" title="Zoom in"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg></button>
                    <button type="button" onclick="fitFlow(this)" class="p-0.5 text-muted-foreground hover:text-foreground transition-colors ml-0.5" title="Fit to view"><svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4h4m8 0h4v4m0 8v4h-4m-8 0H4v-4" /></svg></button>
                </div>
                <div data-flow-clip class="overflow-hidden cursor-grab active:cursor-grabbing">${flowHtml}</div>
            </div>`;
        }

        function applyFlowScale(wrapper, scale) {
            const content = wrapper.querySelector('[data-flow-content]');
            const clip = wrapper.querySelector('[data-flow-clip]');
            const label = wrapper.querySelector('[data-flow-zoom-label]');
            const panX = content._panX || 0;
            const panY = content._panY || 0;
            content.style.transform = 'translate(' + panX + 'px,' + panY + 'px) scale(' + scale + ')';
            clip.style.height = (content.offsetHeight * scale) + 'px';
            label.textContent = Math.round(scale * 100) + '%';
            content._flowScale = scale;
        }

        function resetFlowPan(wrapper) {
            const content = wrapper.querySelector('[data-flow-content]');
            if (content) { content._panX = 0; content._panY = 0; }
        }

        function zoomFlow(btn, delta) {
            const wrapper = btn.closest('[data-flow-wrapper]');
            const content = wrapper.querySelector('[data-flow-content]');
            let scale = Math.round(((content._flowScale || 1) + delta) * 10) / 10;
            scale = Math.max(0.3, Math.min(1.5, scale));
            applyFlowScale(wrapper, scale);
        }

        function fitFlow(btn) {
            const wrapper = btn.closest('[data-flow-wrapper]');
            const content = wrapper.querySelector('[data-flow-content]');
            const clip = wrapper.querySelector('[data-flow-clip]');
            content.style.transform = 'scale(1)';
            clip.style.height = 'auto';
            const scale = Math.min(1, clip.clientWidth / content.scrollWidth);
            resetFlowPan(wrapper);
            applyFlowScale(wrapper, scale);
        }

        function autoFitFlows() {
            document.querySelectorAll('[data-flow-wrapper]').forEach(wrapper => {
                fitFlow(wrapper.querySelector('[data-flow-zoom-label]'));
            });
        }

        window.addEventListener('resize', () => { clearTimeout(window._flowResizeTimer); window._flowResizeTimer = setTimeout(autoFitFlows, 150); });

        // Drag-to-pan for flow clips
        document.addEventListener('mousedown', function(e) {
            const clip = e.target.closest('[data-flow-clip]');
            if (!clip || e.target.closest('button')) return;
            const content = clip.querySelector('[data-flow-content]');
            if (!content) return;
            e.preventDefault();
            clip.style.cursor = 'grabbing';
            const startX = e.clientX, startY = e.clientY;
            const origPanX = content._panX || 0, origPanY = content._panY || 0;
            function onMove(ev) {
                content._panX = origPanX + (ev.clientX - startX);
                content._panY = origPanY + (ev.clientY - startY);
                const s = content._flowScale || 1;
                content.style.transform = 'translate(' + content._panX + 'px,' + content._panY + 'px) scale(' + s + ')';
            }
            function onUp() {
                clip.style.cursor = '';
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onUp);
            }
            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        });

        // Touch drag-to-pan
        document.addEventListener('touchstart', function(e) {
            const clip = e.target.closest('[data-flow-clip]');
            if (!clip || e.target.closest('button') || e.touches.length !== 1) return;
            const content = clip.querySelector('[data-flow-content]');
            if (!content) return;
            const touch = e.touches[0];
            const startX = touch.clientX, startY = touch.clientY;
            const origPanX = content._panX || 0, origPanY = content._panY || 0;
            let moved = false;
            function onMove(ev) {
                const t = ev.touches[0];
                content._panX = origPanX + (t.clientX - startX);
                content._panY = origPanY + (t.clientY - startY);
                const s = content._flowScale || 1;
                content.style.transform = 'translate(' + content._panX + 'px,' + content._panY + 'px) scale(' + s + ')';
                moved = true;
                ev.preventDefault();
            }
            function onEnd() {
                document.removeEventListener('touchmove', onMove);
                document.removeEventListener('touchend', onEnd);
            }
            document.addEventListener('touchmove', onMove, {passive: false});
            document.addEventListener('touchend', onEnd);
        }, {passive: true});

        function toggleDefExpand(defName) {
            expandedDef = expandedDef === defName ? null : defName;
            if (window._lastDefs) renderWorkflowDefinitions(window._lastDefs);
        }

        const tabPollers = {
            jobs: [],
            batches: [
                { fn: loadStationBatches, interval: 5000 },
            ],
            workflows: [
                { fn: loadStationWorkflows, interval: 5000 },
                { fn: loadWorkflowDefinitions, interval: 10000 },
            ],
            workers: [
                { fn: checkWorkerStatus, interval: 10000 },
                { fn: checkSupervisorStatus, interval: 10000 },
            ],
        };

        function startTabPolling(tabName) {
            tabIntervals.forEach(id => clearInterval(id));
            tabIntervals = [];
            const pollers = tabPollers[tabName] || [];
            for (const { fn, interval } of pollers) {
                fn();
                tabIntervals.push(setInterval(fn, interval));
            }
        }

        function switchTab(tabName, save = true) {
            currentTab = tabName;
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('border-emerald-500', 'text-emerald-400');
                btn.classList.add('border-transparent', 'text-muted-foreground');
            });
            const activeBtn = document.getElementById(`tab-${tabName}`);
            if (activeBtn) {
                activeBtn.classList.remove('border-transparent', 'text-muted-foreground');
                activeBtn.classList.add('border-emerald-500', 'text-emerald-400');
            }
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            const contentEl = document.getElementById(`content-${tabName}`);
            if (contentEl) contentEl.classList.add('active');
            startTabPolling(tabName);
            if (save) localStorage.setItem('station_example_tab', tabName);
        }

        function loadSavedTab() {
            const savedTab = localStorage.getItem('station_example_tab');
            if (savedTab && document.getElementById(`tab-${savedTab}`)) {
                switchTab(savedTab, false);
            } else {
                startTabPolling('jobs');
            }
        }

        // Drivers
        async function checkDrivers() {
            try {
                const response = await fetch('/drivers');
                const data = await response.json();
                renderDriverStatus(data.drivers, data.default);
            } catch (error) {
                console.error('Failed to check drivers:', error);
            }
        }

        let defaultDriver = 'sync';

        function renderDriverStatus(drivers, defaultConnection) {
            const container = document.getElementById('driver-status');
            container.innerHTML = '';
            defaultDriver = defaultConnection || 'sync';

            const driverConfig = {
                rabbitmq: { name: 'RabbitMQ', icon: 'M12 19l9 2-9-18-9 18 9-2zm0 0v-8' },
                redis: { name: 'Redis', icon: 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4' },
                sqs: { name: 'SQS', icon: 'M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12' },
                beanstalkd: { name: 'Beanstalkd', icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10' },
                kafka: { name: 'Kafka', icon: 'M13 10V3L4 14h7v7l9-11h-7z' }
            };

            for (const [key, info] of Object.entries(drivers)) {
                const isConnected = info.status === 'connected';
                const isDefault = key === defaultConnection;
                const config = driverConfig[key] || { name: key, icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' };
                const statusClasses = isConnected ? 'border-emerald-500/30' : 'border-red-500/30';
                const dotClass = isConnected ? 'bg-emerald-500' : 'bg-red-500';
                const textClass = isConnected ? 'text-emerald-400' : 'text-red-400';

                const dashboardLink = info.dashboard_url && isConnected
                    ? `<a href="${info.dashboard_url}" target="_blank" rel="noopener noreferrer" class="text-slate-500 hover:text-blue-400 transition-colors ml-0.5" title="Open ${config.name} Dashboard"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg></a>`
                    : '';

                const defaultBadge = isDefault
                    ? '<span class="text-[10px] px-1.5 py-0.5 rounded bg-purple-500/10 text-purple-400 font-medium">DEFAULT</span>'
                    : '';

                container.innerHTML += `
                    <div class="rounded-lg border p-4 ${statusClasses} transition-all cursor-default">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="relative flex h-2.5 w-2.5 flex-shrink-0">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full ${dotClass} opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 ${dotClass}"></span>
                            </span>
                            <span class="font-semibold text-sm truncate">${config.name}</span>
                            ${dashboardLink}
                        </div>
                        <div class="flex items-center justify-between mt-2 gap-1 flex-wrap">
                            <span class="text-xs ${textClass} font-medium capitalize">${info.status}</span>
                            ${defaultBadge}
                        </div>
                    </div>
                `;
            }
        }

        // Dispatch form
        document.getElementById('dispatch-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('dispatch-btn');
            btn.disabled = true;
            btn.textContent = 'Dispatching...';

            const formData = {
                driver: document.getElementById('driver').value,
                count: parseInt(document.getElementById('count').value),
                queue: document.getElementById('queue').value,
                delay: parseInt(document.getElementById('delay').value),
                fail_percentage: parseInt(document.getElementById('fail_percentage').value)
            };

            try {
                const response = await fetch('/dispatch', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify(formData)
                });
                const data = await response.json();
                if (data.success) loadBatches();
            } catch (error) {
                console.error('Dispatch failed:', error);
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg> Create Jobs';
            }
        });

        // Batch history (cache-based)
        async function loadBatches() {
            try {
                const params = new URLSearchParams();
                const conn = document.getElementById('job-history-connection-filter').value;
                const status = document.getElementById('job-history-status-filter').value;
                if (conn) params.set('connection', conn);
                if (status) params.set('status', status);
                const url = '/batches' + (params.toString() ? '?' + params : '');
                const response = await fetch(url);
                const data = await response.json();
                allJobBatches = data.batches || [];
                renderBatches(allJobBatches);
            } catch (error) {
                console.error('Failed to load batches:', error);
            }
        }

        function renderBatches(batches) {
            const container = document.getElementById('batch-history');
            if (batches.length === 0) {
                container.innerHTML = '<div class="flex flex-col items-center justify-center py-12 text-center"><svg class="h-10 w-10 text-muted-foreground/30 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg><p class="text-sm text-muted-foreground">No batches yet</p></div>';
                return;
            }
            container.innerHTML = batches.map(batch => {
                const isComplete = batch.is_complete;
                const hasFailed = batch.failed > 0;
                const progressBg = isComplete ? (hasFailed ? 'bg-amber-500' : 'bg-emerald-500') : 'bg-blue-500';
                const jobStatusLabel = isComplete ? (hasFailed ? 'failed' : 'completed') : 'pending';
                const jobStatusColors = { pending: 'text-amber-400 bg-amber-500/10', completed: 'text-emerald-400 bg-emerald-500/10', failed: 'text-red-400 bg-red-500/10' };
                const jobStatusClass = jobStatusColors[jobStatusLabel] || jobStatusColors.pending;
                return `
                    <div class="rounded-lg border border-border p-4 transition-all">
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-2 flex-wrap">
                                <code class="rounded bg-secondary px-2 py-0.5 font-mono text-xs">${batch.id.substring(0, 8)}...</code>
                                <span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium ${jobStatusClass}">${jobStatusLabel}</span>
                                ${batch.driver ? `<span class="inline-flex items-center rounded bg-secondary px-2 py-0.5 text-xs font-medium text-muted-foreground">${batch.driver}</span>` : ''}
                                ${batch.queue ? `<span class="inline-flex items-center rounded bg-secondary px-2 py-0.5 text-xs font-medium text-muted-foreground">${batch.queue}</span>` : ''}
                            </div>
                            <span class="text-xs text-muted-foreground">${new Date(batch.started_at).toLocaleTimeString()}</span>
                        </div>
                        <div class="mt-3 flex gap-6 text-sm">
                            <span class="text-muted-foreground">Total: <span class="text-foreground font-semibold tabular-nums">${batch.total.toLocaleString()}</span></span>
                            <span class="text-emerald-400 font-medium tabular-nums">\u2713 ${batch.completed.toLocaleString()}</span>
                            <span class="text-red-400 font-medium tabular-nums">\u2717 ${batch.failed.toLocaleString()}</span>
                            <span class="text-blue-400 font-medium tabular-nums">\u23F3 ${batch.pending.toLocaleString()}</span>
                        </div>
                        <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-secondary">
                            <div class="h-full ${progressBg} transition-all rounded-full" style="width: ${batch.progress}%"></div>
                        </div>
                    </div>
                `;
            }).join('');

            const hasPending = allJobBatches.some(b => b.pending > 0);
            if (hasPending && !batchRefreshInterval) {
                batchRefreshInterval = setInterval(loadBatches, 2000);
            } else if (!hasPending && batchRefreshInterval) {
                clearInterval(batchRefreshInterval);
                batchRefreshInterval = null;
            }
        }

        document.getElementById('clear-batches').addEventListener('click', async () => {
            if (!confirm('Clear all batch history?')) return;
            try {
                await fetch('/batches/clear', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } });
                loadBatches();
            } catch (error) {
                console.error('Failed to clear batches:', error);
            }
        });

        // Station Batch form
        document.getElementById('station-batch-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('station-batch-btn');
            btn.disabled = true;
            btn.textContent = 'Creating...';

            const formData = {
                name: document.getElementById('batch-name').value || null,
                count: parseInt(document.getElementById('batch-count').value),
                connection: document.getElementById('batch-connection').value,
                queue: document.getElementById('batch-queue').value,
                allowed_failures: parseInt(document.getElementById('batch-allowed-failures').value),
                fail_percentage: parseInt(document.getElementById('batch-fail-percentage').value || '0'),
            };

            try {
                const response = await fetch('/api/station/batch', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify(formData)
                });
                const data = await response.json();
                if (data.success) loadStationBatches();
                else alert('Failed to create batch: ' + (data.error || 'Unknown error'));
            } catch (error) {
                console.error('Station batch creation failed:', error);
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg> Create Batch';
            }
        });

        // Station batches
        async function loadStationBatches(page) {
            page = page || 1;
            try {
                const params = new URLSearchParams();
                params.set('page', page);
                const conn = document.getElementById('batch-history-connection-filter').value;
                const status = document.getElementById('batch-history-status-filter').value;
                if (conn) params.set('connection', conn);
                if (status) params.set('status', status);
                const url = '/api/station/batches?' + params;
                const response = await fetch(url);
                const data = await response.json();
                renderStationBatches(data.batches || []);
                renderPagination('station-batches-pagination', data.pagination, loadStationBatches);
            } catch (error) { console.error('Failed to load Station batches:', error); }
        }

        function renderStationBatches(batches) {
            const container = document.getElementById('station-batches');
            if (batches.length === 0) {
                container.innerHTML = '<div class="flex flex-col items-center justify-center py-12 text-center"><svg class="h-10 w-10 text-muted-foreground/30 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z" /></svg><p class="text-sm text-muted-foreground">No batches yet</p></div>';
                return;
            }
            container.innerHTML = batches.map(batch => {
                const statusColors = {
                    pending: 'text-amber-400 bg-amber-500/10',
                    running: 'text-blue-400 bg-blue-500/10',
                    completed: 'text-emerald-400 bg-emerald-500/10',
                    failed: 'text-red-400 bg-red-500/10',
                    cancelled: 'text-zinc-400 bg-zinc-500/10'
                };
                const statusClass = statusColors[batch.status] || statusColors.pending;
                const isFinished = ['completed', 'cancelled'].includes(batch.status);
                const hasFailed = (batch.failed_jobs || 0) > 0;

                const cancelBtn = !isFinished
                    ? `<button onclick="cancelBatch('${batch.id}')" class="inline-flex items-center justify-center gap-1 rounded text-xs font-medium h-6 px-2 border border-border text-muted-foreground hover:bg-secondary hover:text-red-400 transition-colors">Cancel</button>`
                    : '';
                const retryBtn = hasFailed
                    ? `<button onclick="retryBatch('${batch.id}')" class="inline-flex items-center justify-center gap-1 rounded text-xs font-medium h-6 px-2 border border-border text-muted-foreground hover:bg-secondary hover:text-amber-400 transition-colors">Retry Failed</button>`
                    : '';

                return `
                    <div class="rounded-lg border border-border p-4 transition-all">
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-semibold text-sm">${batch.name || 'Unnamed Batch'}</span>
                                <span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium ${statusClass}">${batch.status}</span>
                                ${batch.connection ? `<span class="inline-flex items-center rounded bg-secondary px-2 py-0.5 text-xs font-medium text-muted-foreground">${batch.connection}</span>` : ''}
                                ${batch.queue ? `<span class="inline-flex items-center rounded bg-secondary px-2 py-0.5 text-xs font-medium text-muted-foreground">${batch.queue}</span>` : ''}
                            </div>
                            <div class="flex items-center gap-2">
                                ${cancelBtn}${retryBtn}
                                <span class="text-xs text-muted-foreground">${batch.created_at ? new Date(batch.created_at).toLocaleString() : ''}</span>
                            </div>
                        </div>
                        <div class="mt-3 flex gap-6 text-sm">
                            <span class="text-muted-foreground">Total: <span class="text-foreground font-semibold tabular-nums">${(batch.total_jobs || 0).toLocaleString()}</span></span>
                            <span class="text-emerald-400 font-medium tabular-nums">\u2713 ${(batch.completed_jobs || 0).toLocaleString()}</span>
                            <span class="text-red-400 font-medium tabular-nums">\u2717 ${(batch.failed_jobs || 0).toLocaleString()}</span>
                            <span class="text-blue-400 font-medium tabular-nums">\u23F3 ${(batch.pending_jobs || 0).toLocaleString()}</span>
                        </div>
                        <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-secondary">
                            <div class="h-full bg-orange-500 transition-all rounded-full" style="width: ${batch.progress || 0}%"></div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        async function cancelBatch(id) {
            if (!confirm('Cancel this batch?')) return;
            try {
                await fetch(`/api/station/batch/${id}/cancel`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } });
                loadStationBatches();
            } catch (error) { console.error('Failed to cancel batch:', error); }
        }

        async function retryBatch(id) {
            try {
                await fetch(`/api/station/batch/${id}/retry`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } });
                loadStationBatches();
            } catch (error) { console.error('Failed to retry batch:', error); }
        }

        // Station workflows
        async function loadStationWorkflows(page) {
            page = page || 1;
            try {
                const params = new URLSearchParams();
                params.set('page', page);
                const conn = document.getElementById('workflow-history-connection-filter').value;
                const status = document.getElementById('workflow-history-status-filter').value;
                if (conn) params.set('connection', conn);
                if (status) params.set('status', status);
                const url = '/api/station/workflows?' + params;
                const response = await fetch(url);
                const data = await response.json();
                renderStationWorkflows(data.workflows || []);
                renderPagination('station-workflows-pagination', data.pagination, loadStationWorkflows);
            } catch (error) { console.error('Failed to load Station workflows:', error); }
        }

        function renderStationWorkflows(workflows) {
            const container = document.getElementById('station-workflows');
            if (workflows.length === 0) {
                container.innerHTML = '<div class="flex flex-col items-center justify-center py-12 text-center"><svg class="h-10 w-10 text-muted-foreground/30 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" /></svg><p class="text-sm text-muted-foreground">No workflows yet</p></div>';
                return;
            }
            container.innerHTML = workflows.map(workflow => {
                const statusColors = {
                    pending: 'text-amber-400 bg-amber-500/10',
                    running: 'text-blue-400 bg-blue-500/10',
                    completed: 'text-emerald-400 bg-emerald-500/10',
                    failed: 'text-red-400 bg-red-500/10',
                    cancelled: 'text-zinc-400 bg-zinc-500/10'
                };
                const statusClass = statusColors[workflow.status] || statusColors.pending;
                const isFinished = ['completed', 'failed', 'cancelled'].includes(workflow.status);
                const progress = workflow.progress || 0;

                const cancelBtn = !isFinished
                    ? `<button onclick="cancelWorkflow('${workflow.id}')" class="inline-flex items-center justify-center gap-1 rounded text-xs font-medium h-6 px-2 border border-border text-muted-foreground hover:bg-secondary hover:text-red-400 transition-colors">Cancel</button>`
                    : '';

                const errorHtml = workflow.error
                    ? `<p class="text-xs text-red-400 mt-2 truncate" title="${workflow.error}">${workflow.error}</p>`
                    : '';

                const currentStepHtml = workflow.current_step
                    ? `<span class="text-xs text-muted-foreground ml-2">Step: ${workflow.current_step}</span>`
                    : '';

                // Step statuses
                let stepsHtml = '';
                if (workflow.step_statuses && Object.keys(workflow.step_statuses).length > 0) {
                    const stepStatusColors = { completed: 'bg-emerald-500', running: 'bg-blue-500', failed: 'bg-red-500', pending: 'bg-zinc-600' };
                    stepsHtml = '<div class="flex gap-1 mt-2">' +
                        Object.entries(workflow.step_statuses).map(([name, status]) => {
                            const color = stepStatusColors[status] || stepStatusColors.pending;
                            return `<div class="h-1.5 flex-1 rounded-full ${color}" title="${name}: ${status}"></div>`;
                        }).join('') +
                        '</div>';
                }

                return `
                    <div class="rounded-lg border border-border p-4 transition-all">
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-semibold text-sm">${workflow.name || 'Unnamed Workflow'}</span>
                                <span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium ${statusClass}">${workflow.status}</span>
                                ${workflow.connection ? `<span class="inline-flex items-center rounded bg-secondary px-2 py-0.5 text-xs font-medium text-muted-foreground">${workflow.connection}</span>` : ''}
                                ${workflow.queue ? `<span class="inline-flex items-center rounded bg-secondary px-2 py-0.5 text-xs font-medium text-muted-foreground">${workflow.queue}</span>` : ''}
                                ${currentStepHtml}
                            </div>
                            <div class="flex items-center gap-2">
                                ${cancelBtn}
                                <span class="text-xs text-muted-foreground">${workflow.started_at ? new Date(workflow.started_at).toLocaleString() : ''}</span>
                            </div>
                        </div>
                        ${progress > 0 ? `<div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-secondary"><div class="h-full bg-pink-500 transition-all rounded-full" style="width: ${progress}%"></div></div><div class="text-xs text-muted-foreground mt-1">${progress}% complete</div>` : ''}
                        ${stepsHtml}
                        ${errorHtml}
                        ${workflow.completed_at ? `<div class="mt-2 text-xs text-muted-foreground">Completed: ${new Date(workflow.completed_at).toLocaleString()}</div>` : ''}
                    </div>
                `;
            }).join('');
        }

        async function cancelWorkflow(id) {
            if (!confirm('Cancel this workflow?')) return;
            try {
                await fetch(`/api/station/workflow/${id}/cancel`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } });
                loadStationWorkflows();
            } catch (error) { console.error('Failed to cancel workflow:', error); }
        }

        // ── Workflow Definitions ──────────────────────────────────
        let defSteps = [{ name: '', job_class: '', dependencies: [], isParallel: false, subSteps: [] }];
        let editingDefName = null;

        function defToFormSteps(steps) {
            const entries = Object.entries(steps);
            const virtualNames = new Set();
            const parallelGroupNames = new Set();
            const parallelGroups = {};
            for (const [name, step] of entries) {
                if (step.virtual) virtualNames.add(name);
                if (step.parallel_group) {
                    parallelGroupNames.add(step.parallel_group);
                    if (!parallelGroups[step.parallel_group]) parallelGroups[step.parallel_group] = [];
                    parallelGroups[step.parallel_group].push(step);
                }
            }
            // Resolve a dependency: keep it if it's a real step or a parallel group name,
            // drop it only if it's a virtual step that is NOT a group name
            function resolveDeps(deps) {
                return (deps || []).filter(d => !virtualNames.has(d) || parallelGroupNames.has(d));
            }
            const result = [];
            const processed = new Set();
            for (const [name, step] of entries) {
                if (processed.has(name)) continue;
                if (step.virtual) { processed.add(name); continue; }
                if (step.parallel_group && !processed.has(step.parallel_group)) {
                    const group = parallelGroups[step.parallel_group];
                    result.push({
                        name: step.parallel_group,
                        isParallel: true,
                        dependencies: resolveDeps(group[0].dependencies),
                        job_class: '',
                        subSteps: group.map(s => ({ name: s.name, job_class: s.job_class || '' })),
                    });
                    group.forEach(s => processed.add(s.name));
                    processed.add(step.parallel_group);
                } else if (!step.parallel_group) {
                    result.push({
                        name: step.name,
                        job_class: step.job_class || '',
                        dependencies: resolveDeps(step.dependencies),
                        isParallel: false,
                        subSteps: [],
                    });
                    processed.add(name);
                }
            }
            return result;
        }

        function editDefinition(def) {
            editingDefName = def.name;
            document.getElementById('def-name').value = def.name;
            document.getElementById('def-description').value = def.description || '';
            document.getElementById('def-timeout').value = def.timeout || 300;
            document.getElementById('def-connection').value = def.connection || '';
            defSteps = defToFormSteps(def.steps || {});
            if (defSteps.length === 0) defSteps = [{ name: '', job_class: '', dependencies: [], isParallel: false, subSteps: [] }];
            document.getElementById('def-form-title').textContent = 'Edit Workflow Definition';
            document.getElementById('submit-def-btn').textContent = 'Save Changes';
            const wrapper = document.getElementById('def-form-wrapper');
            wrapper.classList.remove('hidden');
            renderDefSteps();
            wrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function renderDefSteps() {
            const container = document.getElementById('def-steps');
            const allNames = defSteps.map(s => s.name).filter(Boolean);

            container.innerHTML = defSteps.map((step, idx) => {
                const depButtons = allNames.filter(n => n !== step.name).map(n => {
                    const active = step.dependencies.includes(n);
                    return `<button type="button" onclick="toggleDefDep(${idx}, '${n}')" class="${active ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-500/20 dark:text-indigo-400 ring-1 ring-indigo-300 dark:ring-indigo-500/40' : 'bg-secondary text-muted-foreground hover:bg-secondary/80'} px-2 py-0.5 rounded text-xs font-medium transition-colors">${n}</button>`;
                }).join('');

                const depsHtml = allNames.filter(n => n !== step.name).length > 0
                    ? `<div class="mt-2"><label class="text-[10px] text-muted-foreground block mb-1">Dependencies</label><div class="flex flex-wrap gap-1.5">${depButtons}</div></div>`
                    : '';

                if (step.isParallel) {
                    const subStepsHtml = step.subSteps.map((sub, subIdx) => `
                        <div class="flex items-center gap-2 mt-1">
                            <input type="text" value="${sub.name}" onchange="defSteps[${idx}].subSteps[${subIdx}].name=this.value;updateDefPreview()" placeholder="sub-step name" class="flex-1 h-8 rounded-lg border border-input bg-background px-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                            <input type="text" value="${sub.job_class}" onchange="defSteps[${idx}].subSteps[${subIdx}].job_class=this.value" placeholder="Job class (optional)" class="flex-1 h-8 rounded-lg border border-input bg-background px-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                            <button type="button" onclick="removeDefSubStep(${idx},${subIdx})" class="p-1 text-muted-foreground hover:text-red-500"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
                        </div>
                    `).join('');

                    return `<div data-def-step="${idx}" class="border border-border rounded-lg bg-secondary/20 transition-shadow" ondragover="onDefDragOver(event,${idx})" ondrop="onDefDrop(event,${idx})">
                        <div class="flex items-center gap-2 px-3 py-2 border-b border-border bg-secondary/40 rounded-t-lg cursor-grab active:cursor-grabbing" draggable="true" ondragstart="onDefDragStart(event,${idx})" ondragend="onDefDragEnd(event)">
                            <svg class="h-4 w-4 text-muted-foreground/60 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16" /></svg>
                            <span class="text-[10px] font-medium text-muted-foreground bg-secondary px-2 py-0.5 rounded">Parallel Group</span>
                            <span class="flex-1"></span>
                            <button type="button" onclick="removeDefStep(${idx})" class="p-1 text-muted-foreground hover:text-red-500 transition-colors"><svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                        </div>
                        <div class="p-4">
                            <div class="mt-0"><input type="text" value="${step.name}" onchange="defSteps[${idx}].name=this.value;renderDefSteps()" placeholder="group-name" class="h-8 w-full rounded-lg border border-input bg-background px-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50"></div>
                            ${depsHtml}
                            <div class="mt-2"><label class="text-[10px] text-muted-foreground block mb-1">Sub-steps</label>${subStepsHtml}
                                <button type="button" onclick="addDefSubStep(${idx})" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline mt-1 pl-px">+ Add Sub-step</button>
                            </div>
                        </div>
                    </div>`;
                }

                return `<div data-def-step="${idx}" class="border border-border rounded-lg bg-secondary/20 transition-shadow" ondragover="onDefDragOver(event,${idx})" ondrop="onDefDrop(event,${idx})">
                    <div class="flex items-center gap-2 px-3 py-2 border-b border-border bg-secondary/40 rounded-t-lg cursor-grab active:cursor-grabbing" draggable="true" ondragstart="onDefDragStart(event,${idx})" ondragend="onDefDragEnd(event)">
                        <svg class="h-4 w-4 text-muted-foreground/60 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16" /></svg>
                        <span class="text-[10px] font-medium text-muted-foreground bg-secondary px-2 py-0.5 rounded">Step ${idx + 1}</span>
                        <span class="flex-1"></span>
                        <button type="button" onclick="removeDefStep(${idx})" class="p-1 text-muted-foreground hover:text-red-500 transition-colors"><svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <input type="text" value="${step.name}" onchange="defSteps[${idx}].name=this.value;renderDefSteps()" placeholder="step-name" class="h-8 rounded-lg border border-input bg-background px-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                            <input type="text" value="${step.job_class}" onchange="defSteps[${idx}].job_class=this.value" placeholder="Job class (optional)" class="h-8 rounded-lg border border-input bg-background px-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/50">
                        </div>
                        ${depsHtml}
                    </div>
                </div>`;
            }).map((html, idx) => {
                if (idx === 0) return html;
                const menuId = 'insert-menu-' + idx;
                return `<div class="flex items-center gap-2 py-0.5 group/insert relative">
                    <div class="flex-1 border-t border-dashed border-transparent group-hover/insert:border-border transition-colors"></div>
                    <button type="button" onclick="toggleInsertMenu('${menuId}', event)" class="p-0.5 rounded text-muted-foreground/40 hover:text-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition-colors" title="Insert here">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    </button>
                    <div class="flex-1 border-t border-dashed border-transparent group-hover/insert:border-border transition-colors"></div>
                    <div id="${menuId}" data-insert-menu class="hidden absolute left-1/2 -translate-x-1/2 top-full z-10 mt-1 rounded-lg border border-border bg-card shadow-lg py-1 min-w-[160px]">
                        <button type="button" onclick="insertDefStepAt(${idx})" class="w-full text-left px-3 py-1.5 text-xs text-foreground hover:bg-secondary transition-colors">+ Step</button>
                        <button type="button" onclick="insertDefParallelAt(${idx})" class="w-full text-left px-3 py-1.5 text-xs text-foreground hover:bg-secondary transition-colors">+ Parallel Group</button>
                    </div>
                </div>` + html;
            }).join('');

            updateDefPreview();
        }

        function updateDefPreview() {
            const named = defSteps.filter(s => s.name);
            const preview = document.getElementById('def-preview');
            const content = document.getElementById('def-preview-content');
            if (!named.length) { preview.classList.add('hidden'); return; }
            preview.classList.remove('hidden');
            content.innerHTML = named.map((step, idx) => {
                const arrow = idx > 0 ? '<svg class="h-4 w-4 text-muted-foreground inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>' : '';
                if (step.isParallel) {
                    const subs = step.subSteps.filter(s => s.name).map(s => s.name).join(', ');
                    return `${arrow}<span class="bg-indigo-100 dark:bg-indigo-500/10 text-indigo-800 dark:text-indigo-400 px-2 py-0.5 rounded text-xs">${step.name} [${subs}]</span>`;
                }
                const deps = step.dependencies.length ? `<span class="text-muted-foreground ml-1">(after: ${step.dependencies.join(', ')})</span>` : '';
                return `${arrow}<span class="bg-secondary text-foreground px-2 py-0.5 rounded text-xs">${step.name}${deps}</span>`;
            }).join('');
        }

        function toggleDefDep(stepIdx, depName) {
            const deps = defSteps[stepIdx].dependencies;
            const i = deps.indexOf(depName);
            if (i >= 0) deps.splice(i, 1); else deps.push(depName);
            renderDefSteps();
        }

        function addDefStep() {
            defSteps.push({ name: '', job_class: '', dependencies: [], isParallel: false, subSteps: [] });
            renderDefSteps();
        }

        function addDefParallel() {
            defSteps.push({ name: '', job_class: '', dependencies: [], isParallel: true, subSteps: [{ name: '', job_class: '' }, { name: '', job_class: '' }] });
            renderDefSteps();
        }

        function insertDefStepAt(pos) {
            defSteps.splice(pos, 0, { name: '', job_class: '', dependencies: [], isParallel: false, subSteps: [] });
            renderDefSteps();
        }

        function insertDefParallelAt(pos) {
            defSteps.splice(pos, 0, { name: '', job_class: '', dependencies: [], isParallel: true, subSteps: [{ name: '', job_class: '' }, { name: '', job_class: '' }] });
            renderDefSteps();
        }

        function toggleInsertMenu(id, e) {
            e.stopPropagation();
            const menu = document.getElementById(id);
            const wasHidden = menu.classList.contains('hidden');
            document.querySelectorAll('[data-insert-menu]').forEach(m => m.classList.add('hidden'));
            if (wasHidden) menu.classList.remove('hidden');
        }

        document.addEventListener('click', () => {
            document.querySelectorAll('[data-insert-menu]').forEach(m => m.classList.add('hidden'));
        });

        function removeDefStep(idx) {
            defSteps.splice(idx, 1);
            if (defSteps.length === 0) defSteps.push({ name: '', job_class: '', dependencies: [], isParallel: false, subSteps: [] });
            renderDefSteps();
        }

        function addDefSubStep(stepIdx) {
            defSteps[stepIdx].subSteps.push({ name: '', job_class: '' });
            renderDefSteps();
        }

        function removeDefSubStep(stepIdx, subIdx) {
            defSteps[stepIdx].subSteps.splice(subIdx, 1);
            renderDefSteps();
        }

        let dragSrcIdx = null;
        function onDefDragStart(e, idx) {
            dragSrcIdx = idx;
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', idx);
            e.currentTarget.closest('[data-def-step]').classList.add('opacity-40');
        }
        function onDefDragEnd(e) {
            e.currentTarget.closest('[data-def-step]').classList.remove('opacity-40');
            document.querySelectorAll('[data-def-step]').forEach(el => el.classList.remove('ring-2', 'ring-indigo-500/50'));
            dragSrcIdx = null;
        }
        function onDefDragOver(e, idx) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            document.querySelectorAll('[data-def-step]').forEach(el => el.classList.remove('ring-2', 'ring-indigo-500/50'));
            if (idx !== dragSrcIdx) e.currentTarget.closest('[data-def-step]').classList.add('ring-2', 'ring-indigo-500/50');
        }
        function onDefDrop(e, idx) {
            e.preventDefault();
            if (dragSrcIdx === null || dragSrcIdx === idx) return;
            const moved = defSteps.splice(dragSrcIdx, 1)[0];
            defSteps.splice(idx, 0, moved);
            dragSrcIdx = null;
            renderDefSteps();
        }

        function resetDefForm() {
            editingDefName = null;
            document.getElementById('def-name').value = '';
            document.getElementById('def-description').value = '';
            document.getElementById('def-timeout').value = '300';
            document.getElementById('def-connection').value = '';
            document.getElementById('def-form-error').classList.add('hidden');
            document.getElementById('def-form-title').textContent = 'Create Workflow Definition';
            document.getElementById('submit-def-btn').textContent = 'Create Definition';
            defSteps = [{ name: '', job_class: '', dependencies: [], isParallel: false, subSteps: [] }];
            renderDefSteps();
        }

        document.getElementById('toggle-def-form').addEventListener('click', () => {
            const wrapper = document.getElementById('def-form-wrapper');
            const visible = !wrapper.classList.contains('hidden');
            if (visible) {
                wrapper.classList.add('hidden');
            } else {
                wrapper.classList.remove('hidden');
                renderDefSteps();
            }
        });

        document.getElementById('cancel-def-btn').addEventListener('click', () => {
            document.getElementById('def-form-wrapper').classList.add('hidden');
            resetDefForm();
        });

        document.getElementById('add-step-btn').addEventListener('click', addDefStep);
        document.getElementById('add-parallel-btn').addEventListener('click', addDefParallel);

        document.getElementById('submit-def-btn').addEventListener('click', async () => {
            const name = document.getElementById('def-name').value.trim();
            const errorEl = document.getElementById('def-form-error');
            if (!name) { errorEl.textContent = 'Name is required'; errorEl.classList.remove('hidden'); return; }
            if (!defSteps.some(s => s.name.trim())) { errorEl.textContent = 'At least one step with a name is required'; errorEl.classList.remove('hidden'); return; }

            const btn = document.getElementById('submit-def-btn');
            const isEditing = editingDefName !== null;
            btn.disabled = true;
            btn.textContent = isEditing ? 'Saving...' : 'Creating...';
            errorEl.classList.add('hidden');

            const steps = defSteps.filter(s => s.name.trim()).map(s => {
                if (s.isParallel) {
                    return { name: s.name, parallel: true, dependencies: s.dependencies.filter(Boolean), jobs: s.subSteps.filter(ss => ss.name.trim()).map(ss => ({ name: ss.name, job_class: ss.job_class || '' })) };
                }
                return { name: s.name, job_class: s.job_class || '', dependencies: s.dependencies.filter(Boolean) };
            });

            try {
                const url = isEditing
                    ? `/api/station/workflow-definitions/${encodeURIComponent(editingDefName)}`
                    : '/api/station/workflow-definitions';
                const response = await fetch(url, {
                    method: isEditing ? 'PUT' : 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({
                        name,
                        description: document.getElementById('def-description').value.trim() || null,
                        timeout: parseInt(document.getElementById('def-timeout').value) || 300,
                        connection: document.getElementById('def-connection').value || null,
                        steps,
                    }),
                });
                const data = await response.json();
                if (!response.ok) {
                    const msg = data.errors ? Object.values(data.errors).flat().join(', ') : (data.message || (isEditing ? 'Failed to update definition' : 'Failed to create definition'));
                    throw new Error(msg);
                }
                document.getElementById('def-form-wrapper').classList.add('hidden');
                resetDefForm();
                loadWorkflowDefinitions();
            } catch (error) {
                errorEl.textContent = error.message;
                errorEl.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btn.textContent = isEditing ? 'Save Changes' : 'Create Definition';
            }
        });

        async function loadWorkflowDefinitions() {
            try {
                const response = await fetch('/api/station/workflow-definitions');
                const data = await response.json();
                renderWorkflowDefinitions(data.definitions || []);
            } catch (error) { console.error('Failed to load workflow definitions:', error); }
        }

        let defCurrentPage = 1;
        const defPerPage = 10;

        function renderWorkflowDefinitions(defs) {
            window._lastDefs = defs;
            const container = document.getElementById('workflow-definitions');
            document.getElementById('def-count').textContent = `(${defs.length})`;

            if (defs.length === 0) {
                container.innerHTML = '<div class="flex flex-col items-center justify-center py-8 text-center"><svg class="h-8 w-8 text-muted-foreground/30 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" /></svg><p class="text-sm text-muted-foreground">No definitions registered</p><p class="text-xs text-muted-foreground/70 mt-1">Create one above or register in AppServiceProvider</p></div>';
                document.getElementById('workflow-definitions-pagination').innerHTML = '';
                return;
            }

            const totalPages = Math.ceil(defs.length / defPerPage);
            if (defCurrentPage > totalPages) defCurrentPage = totalPages;
            const start = (defCurrentPage - 1) * defPerPage;
            const paginated = defs.slice(start, start + defPerPage);

            container.innerHTML = '<div class="space-y-3">' + paginated.map(def => {
                const source = def.source || 'code';
                const sourceBadge = source === 'database'
                    ? '<span class="bg-purple-100 text-purple-800 dark:bg-purple-500/10 dark:text-purple-400 px-1.5 py-0.5 text-[10px] font-medium rounded uppercase">Custom</span>'
                    : '<span class="bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-400 px-1.5 py-0.5 text-[10px] font-medium rounded uppercase">Code</span>';

                const editBtn = source === 'database'
                    ? `<button onclick='editDefinition(${JSON.stringify(def).replace(/'/g, "&#39;")})' class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded border border-border text-muted-foreground hover:text-indigo-600 hover:border-indigo-300 dark:hover:text-indigo-400 dark:hover:border-indigo-500/40 transition-colors">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                Edit
                            </button>`
                    : '';

                const deleteBtn = source === 'database'
                    ? `<button onclick="deleteWorkflowDefinition('${def.name}')" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded border border-border text-muted-foreground hover:text-red-600 hover:border-red-300 dark:hover:text-red-400 dark:hover:border-red-500/40 transition-colors">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                Delete
                            </button>`
                    : '';

                const steps = def.steps || {};
                const stepNames = Object.keys(steps);
                const isExpanded = expandedDef === def.name;
                const chevron = isExpanded
                    ? '<svg class="h-4 w-4 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>'
                    : '<svg class="h-4 w-4 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>';

                const stepFlowHtml = isExpanded && stepNames.length > 0
                    ? `<div class="border-t border-border pt-3 mt-3">${renderStepFlow(steps)}</div>`
                    : '';

                return `<div class="rounded-lg border border-border p-4">
                    <div class="flex items-center gap-3">
                        <button onclick="toggleDefExpand('${def.name}')" class="flex-shrink-0 p-0.5 rounded hover:bg-secondary transition-colors">${chevron}</button>
                        <div class="flex items-center gap-2 flex-1 min-w-0">
                            <span class="text-sm font-semibold truncate">${def.name}</span>
                            ${sourceBadge}
                            <span class="text-xs text-muted-foreground">${stepNames.length} steps</span>
                            ${def.timeout ? `<span class="text-xs text-muted-foreground">Timeout: ${def.timeout}s</span>` : ''}
                            ${def.connection ? `<span class="bg-emerald-100 text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-400 px-1.5 py-0.5 text-[10px] font-medium rounded uppercase">${def.connection}</span>` : ''}
                        </div>
                        <div class="flex items-center gap-1 flex-shrink-0">
                            <button onclick="runDefinition('${def.name}')" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded border border-border text-muted-foreground hover:text-emerald-600 hover:border-emerald-300 dark:hover:text-emerald-400 dark:hover:border-emerald-500/40 transition-colors">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /></svg>
                                Run
                            </button>
                            ${editBtn}
                            ${deleteBtn}
                        </div>
                    </div>
                    ${stepFlowHtml}
                </div>`;
            }).join('') + '</div>';

            // Client-side pagination for definitions
            if (totalPages > 1) {
                renderPagination('workflow-definitions-pagination', {
                    current_page: defCurrentPage,
                    last_page: totalPages,
                    total: defs.length,
                    per_page: defPerPage,
                }, goToDefPage);
            } else {
                document.getElementById('workflow-definitions-pagination').innerHTML = '';
            }

            requestAnimationFrame(autoFitFlows);
        }

        function goToDefPage(page) {
            defCurrentPage = page;
            if (window._lastDefs) renderWorkflowDefinitions(window._lastDefs);
        }

        async function runDefinition(name) {
            try {
                const response = await fetch('/api/station/workflow-definitions/run', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ definition: name }),
                });
                const data = await response.json();
                if (data.success) {
                    loadStationWorkflows();
                } else {
                    alert('Failed to run definition: ' + (data.error || 'Unknown error'));
                }
            } catch (error) { console.error('Failed to run definition:', error); }
        }

        async function deleteWorkflowDefinition(name) {
            if (!confirm(`Delete the "${name}" definition? This cannot be undone.`)) return;
            try {
                await fetch(`/api/station/workflow-definitions/${encodeURIComponent(name)}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                });
                loadWorkflowDefinitions();
            } catch (error) { console.error('Failed to delete definition:', error); }
        }

        document.getElementById('clear-station-batches').addEventListener('click', async () => {
            if (!confirm('Clear all batch history?')) return;
            try {
                await fetch('/api/station/batches/clear', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } });
                loadStationBatches();
            } catch (error) { console.error('Failed to clear station batches:', error); }
        });

        document.getElementById('clear-station-workflows').addEventListener('click', async () => {
            if (!confirm('Clear all workflow history?')) return;
            try {
                await fetch('/api/station/workflows/clear', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } });
                loadStationWorkflows();
            } catch (error) { console.error('Failed to clear station workflows:', error); }
        });

        // Worker controls
        const spinnerSvg = '<svg class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>';
        const startIcon = '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';

        function showStartingPlaceholder(driver, queue) {
            const card = document.querySelector(`#worker-controls [data-driver="${driver}"]`);
            if (!card) return;
            const statusDot = card.querySelector('.worker-status span');
            const statusText = card.querySelector('.worker-status-text');
            statusDot.className = 'relative inline-flex rounded-full h-2.5 w-2.5 bg-blue-500 animate-pulse';
            statusText.innerHTML = `${spinnerSvg} Starting...`;
            statusText.className = 'text-xs text-blue-400 worker-status-text flex items-center gap-1';
            let container = card.querySelector('.external-workers');
            if (!container) { container = document.createElement('div'); container.className = 'external-workers mt-3 space-y-2'; card.appendChild(container); }
            const placeholder = document.createElement('div');
            placeholder.className = 'flex items-center text-xs bg-blue-500/5 rounded-lg px-3 py-2 border border-blue-500/20 animate-pulse starting-placeholder';
            placeholder.innerHTML = `<div class="flex items-center gap-2"><span class="inline-block w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>${spinnerSvg}<span class="text-blue-400">Starting on <span class="font-mono">${queue}</span>...</span></div>`;
            container.appendChild(placeholder);
        }

        function showStoppingState(pid, driver) {
            const card = document.querySelector(`#worker-controls [data-driver="${driver}"]`);
            if (!card) return;
            const container = card.querySelector('.external-workers');
            if (!container) return;
            const entries = container.querySelectorAll('.flex.items-center');
            for (const entry of entries) {
                if (entry.textContent.includes(`PID ${pid}`)) {
                    entry.style.opacity = '0.6';
                    const btn = entry.querySelector('button');
                    if (btn) { btn.disabled = true; btn.innerHTML = `${spinnerSvg} Stopping...`; btn.className = 'text-red-400/50 px-2 py-1 rounded flex items-center gap-1 text-xs'; }
                    break;
                }
            }
        }

        async function startWorker(driver) {
            const card = document.querySelector(`#worker-controls [data-driver="${driver}"]`);
            const startBtn = card.querySelector('.worker-start-btn');
            startBtn.disabled = true;
            startBtn.innerHTML = `${spinnerSvg} Starting...`;
            try {
                const response = await fetch('/worker/start', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }, body: JSON.stringify({ driver, queue: 'default' }) });
                const data = await response.json();
                if (data.success) { showStartingPlaceholder(driver, 'default'); await checkWorkerStatus(); startBtn.innerHTML = `${startIcon} Quick Start`; }
                else throw new Error(data.error || 'Failed to start worker');
            } catch (error) { console.error('Failed to start worker:', error); alert('Failed to start worker: ' + error.message); startBtn.disabled = false; startBtn.innerHTML = `${startIcon} Quick Start`; }
        }

        async function stopWorker(driver) {
            if (!confirm(`Stop all ${driver} workers?`)) return;
            const card = document.querySelector(`#worker-controls [data-driver="${driver}"]`);
            const stopBtn = card.querySelector('.worker-stop-btn');
            const statusDot = card.querySelector('.worker-status span');
            const statusText = card.querySelector('.worker-status-text');
            stopBtn.disabled = true;
            statusDot.className = 'relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500 animate-pulse';
            statusText.innerHTML = `${spinnerSvg} Stopping all...`;
            statusText.className = 'text-xs text-red-400 worker-status-text flex items-center gap-1';
            try {
                const response = await fetch('/worker/stop', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }, body: JSON.stringify({ driver }) });
                const data = await response.json();
                if (data.success) {
                    delete workerStatus[driver];
                    const startBtn = card.querySelector('.worker-start-btn');
                    const pauseBtn = card.querySelector('.queue-pause-btn');
                    statusDot.className = 'relative inline-flex rounded-full h-2.5 w-2.5 bg-zinc-500';
                    statusText.textContent = 'Stopped';
                    statusText.className = 'text-xs text-muted-foreground worker-status-text';
                    startBtn.disabled = false; stopBtn.disabled = true; pauseBtn.disabled = true;
                    queuePauseStatus[driver] = false;
                    updatePauseButton(driver, false);
                    const ext = card.querySelector('.external-workers');
                    if (ext) ext.innerHTML = '';
                    setTimeout(checkWorkerStatus, 1000);
                } else throw new Error(data.error || 'Failed to stop worker');
            } catch (error) { console.error('Failed to stop worker:', error); checkWorkerStatus(); }
        }

        const queuePauseStatus = {};

        async function togglePause(driver) {
            const isPaused = queuePauseStatus[driver];
            const endpoint = isPaused ? '/queue/resume' : '/queue/pause';
            try {
                const response = await fetch(endpoint, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }, body: JSON.stringify({ driver, queue: 'default' }) });
                const data = await response.json();
                if (data.success) { queuePauseStatus[driver] = data.paused; updatePauseButton(driver, data.paused); checkWorkerStatus(); }
            } catch (error) { console.error('Failed to toggle pause:', error); }
        }

        function updatePauseButton(driver, isPaused) {
            const card = document.querySelector(`#worker-controls [data-driver="${driver}"]`);
            if (!card) return;
            const pauseBtn = card.querySelector('.queue-pause-btn');
            if (!pauseBtn) return;
            if (isPaused) {
                pauseBtn.innerHTML = '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>Resume';
                pauseBtn.title = 'Resume Queue';
            } else {
                pauseBtn.innerHTML = '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>Pause';
                pauseBtn.title = 'Pause Queue';
            }
        }

        async function loadQueuePauseStatus() {
            try {
                const response = await fetch('/queue/pause-status');
                const data = await response.json();
                for (const [driver, isPaused] of Object.entries(data.paused)) { queuePauseStatus[driver] = isPaused; updatePauseButton(driver, isPaused); }
                checkWorkerStatus();
            } catch (error) { console.error('Failed to load pause status:', error); }
        }

        async function checkWorkerStatus() {
            try {
                const response = await fetch('/worker/status');
                const data = await response.json();
                for (const [driver, status] of Object.entries(data.workers || {})) {
                    const card = document.querySelector(`#worker-controls [data-driver="${driver}"]`);
                    if (!card) continue;
                    const startBtn = card.querySelector('.worker-start-btn');
                    const stopBtn = card.querySelector('.worker-stop-btn');
                    const pauseBtn = card.querySelector('.queue-pause-btn');
                    const statusDot = card.querySelector('.worker-status span');
                    const statusText = card.querySelector('.worker-status-text');

                    let existingBadge = card.querySelector('.default-badge');
                    const driverNameContainer = card.querySelector('.flex.items-center.gap-2');
                    if (status.is_default && !existingBadge && driverNameContainer) {
                        const badge = document.createElement('span');
                        badge.className = 'default-badge text-[10px] px-1.5 py-0.5 rounded bg-purple-500/10 text-purple-400 font-medium ml-1';
                        badge.textContent = 'DEFAULT';
                        driverNameContainer.appendChild(badge);
                    }

                    let externalContainer = card.querySelector('.external-workers');
                    if (!externalContainer) { externalContainer = document.createElement('div'); externalContainer.className = 'external-workers mt-3 space-y-2'; card.appendChild(externalContainer); }

                    const hasExternalWorkers = status.external_count > 0;
                    const isUiRunning = status.running;
                    const uiIsSupervisor = status.is_supervisor || false;
                    const uiChildCount = (status.children || []).length;
                    const externalWorkerCount = hasExternalWorkers ? status.external_workers.filter(w => w.role === 'worker').length : 0;
                    const uiWorkerCount = isUiRunning ? (uiIsSupervisor ? uiChildCount : 1) : 0;
                    const totalWorkers = uiWorkerCount + externalWorkerCount;
                    const workerLabel = totalWorkers === 1 ? 'worker' : 'workers';
                    const totalProcesses = (isUiRunning ? 1 : 0) + status.external_count;

                    if (totalWorkers > 0) {
                        workerStatus[driver] = status;
                        const isPaused = queuePauseStatus[driver] || false;
                        if (isPaused) {
                            statusDot.className = 'relative inline-flex rounded-full h-2.5 w-2.5 bg-orange-500';
                            statusText.innerHTML = `Paused (${totalWorkers} ${workerLabel})`;
                            statusText.className = 'text-xs text-orange-400 worker-status-text';
                        } else {
                            statusDot.className = 'relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500';
                            statusText.innerHTML = `Running (${totalWorkers} ${workerLabel})`;
                            statusText.className = 'text-xs text-emerald-400 worker-status-text';
                        }
                        startBtn.disabled = isUiRunning; stopBtn.disabled = false; pauseBtn.disabled = false;
                    } else {
                        delete workerStatus[driver];
                        statusDot.className = 'relative inline-flex rounded-full h-2.5 w-2.5 bg-zinc-500';
                        statusText.innerHTML = 'Stopped';
                        statusText.className = 'text-xs text-muted-foreground worker-status-text';
                        startBtn.disabled = false; stopBtn.disabled = true; pauseBtn.disabled = true;
                    }

                    const stopIcon = '<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" /></svg>';
                    stopBtn.innerHTML = totalProcesses > 1 ? `${stopIcon} Stop All` : `${stopIcon} Stop`;

                    const runningDot = '<span class="inline-block w-2 h-2 rounded-full bg-emerald-500" title="Running"></span>';
                    let workersHtml = '';

                    if (isUiRunning && status.pid) {
                        const uiChildren = status.children || [];
                        const uiQueue = status.queue || 'default';
                        const queueBadge = `<span class="text-[10px] px-1.5 py-0.5 rounded bg-indigo-500/10 text-indigo-400 font-medium">${uiQueue}</span>`;
                        if (uiIsSupervisor) {
                            workersHtml += `<div class="flex items-center justify-between text-xs bg-zinc-500/5 rounded-lg px-3 py-2 border border-zinc-500/10"><div class="flex items-center gap-2">${runningDot}<span class="text-zinc-400 font-mono">PID ${status.pid}</span><span class="text-zinc-500">supervisor</span>${queueBadge}${uiChildren.length > 0 ? `<span class="text-zinc-600">&middot; ${uiChildren.length} worker${uiChildren.length > 1 ? 's' : ''}</span>` : ''}</div><button onclick="stopExternalWorker(${status.pid}, '${driver}')" class="text-red-400/70 hover:text-red-400 hover:bg-red-500/10 px-2 py-1 rounded transition-all">Stop</button></div>`;
                            uiChildren.forEach(childPid => { workersHtml += `<div class="flex items-center text-xs bg-zinc-500/5 rounded-lg px-3 py-2 border border-zinc-500/10 ml-4"><div class="flex items-center gap-2"><span class="text-zinc-500">&larr;</span>${runningDot}<span class="text-zinc-400 font-mono">PID ${childPid}</span></div></div>`; });
                        } else {
                            workersHtml += `<div class="flex items-center justify-between text-xs bg-zinc-500/5 rounded-lg px-3 py-2 border border-zinc-500/10"><div class="flex items-center gap-2">${runningDot}<span class="text-zinc-400 font-mono">PID ${status.pid}</span>${queueBadge}</div><button onclick="stopExternalWorker(${status.pid}, '${driver}')" class="text-red-400/70 hover:text-red-400 hover:bg-red-500/10 px-2 py-1 rounded transition-all">Stop</button></div>`;
                        }
                    }

                    if (hasExternalWorkers) {
                        const supervisors = status.external_workers.filter(w => w.role === 'supervisor');
                        const workers = status.external_workers.filter(w => w.role === 'worker');
                        supervisors.forEach(sup => {
                            const childCount = sup.children ? sup.children.length : 0;
                            const supQueue = sup.queue || 'default';
                            workersHtml += `<div class="flex items-center justify-between text-xs bg-zinc-500/5 rounded-lg px-3 py-2 border border-zinc-500/10"><div class="flex items-center gap-2">${runningDot}<span class="text-zinc-400 font-mono">PID ${sup.pid}</span><span class="text-zinc-500">supervisor</span><span class="text-[10px] px-1.5 py-0.5 rounded bg-indigo-500/10 text-indigo-400 font-medium">${supQueue}</span>${childCount > 0 ? `<span class="text-zinc-600">&middot; ${childCount} worker${childCount > 1 ? 's' : ''}</span>` : ''}</div><button onclick="stopExternalWorker(${sup.pid}, '${driver}')" class="text-red-400/70 hover:text-red-400 hover:bg-red-500/10 px-2 py-1 rounded transition-all">Stop</button></div>`;
                            workers.filter(w => sup.children && sup.children.includes(w.pid)).forEach(child => { workersHtml += `<div class="flex items-center text-xs bg-zinc-500/5 rounded-lg px-3 py-2 border border-zinc-500/10 ml-4"><div class="flex items-center gap-2"><span class="text-zinc-500">&larr;</span>${runningDot}<span class="text-zinc-400 font-mono">PID ${child.pid}</span></div></div>`; });
                        });
                        const childPids = supervisors.flatMap(s => s.children || []);
                        workers.filter(w => !childPids.includes(w.pid)).forEach(w => {
                            const wQueue = w.queue || 'default';
                            workersHtml += `<div class="flex items-center justify-between text-xs bg-zinc-500/5 rounded-lg px-3 py-2 border border-zinc-500/10"><div class="flex items-center gap-2">${runningDot}<span class="text-zinc-400 font-mono">PID ${w.pid}</span><span class="text-[10px] px-1.5 py-0.5 rounded bg-indigo-500/10 text-indigo-400 font-medium">${wQueue}</span></div><button onclick="stopExternalWorker(${w.pid}, '${driver}')" class="text-red-400/70 hover:text-red-400 hover:bg-red-500/10 px-2 py-1 rounded transition-all">Stop</button></div>`;
                        });
                    }
                    externalContainer.innerHTML = workersHtml;
                }
            } catch (error) { console.error('Failed to check worker status:', error); }
            updateBulkButtons();
        }

        async function stopExternalWorker(pid, driver) {
            showStoppingState(pid, driver);
            try {
                const response = await fetch('/worker/stop-external', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }, body: JSON.stringify({ pid }) });
                await checkWorkerStatus();
            } catch (error) { console.error('Failed to stop external worker:', error); await checkWorkerStatus(); }
        }

        function adjustWorkers(delta) {
            const input = document.getElementById('workers-input');
            const current = parseInt(input.value) || 1;
            input.value = Math.max(1, Math.min(10, current + delta));
        }

        async function launchWorker(event) {
            event.preventDefault();
            const form = document.getElementById('launch-worker-form');
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            const connection = formData.get('connection');
            const queue = formData.get('queue');
            const workers = parseInt(formData.get('workers'));

            submitBtn.disabled = true;
            const originalHtml = submitBtn.innerHTML;
            submitBtn.textContent = 'Launching...';

            try {
                const response = await fetch('/worker/launch', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }, body: JSON.stringify({ connection, queue, workers }) });
                const data = await response.json();
                if (data.success) { showStartingPlaceholder(connection, queue); await checkWorkerStatus(); }
                else throw new Error(data.error || 'Failed to launch worker');
            } catch (error) { console.error('Failed to launch worker:', error); alert('Failed to launch worker: ' + error.message); }
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHtml;
        }

        const allDrivers = @json(\Station\Enums\Driver::values());

        function updateBulkButtons() {
            const runningDrivers = Object.keys(workerStatus);
            const hasAnyRunning = runningDrivers.length > 0;
            const allRunning = runningDrivers.length === allDrivers.length;
            const allPaused = hasAnyRunning && runningDrivers.every(d => queuePauseStatus[d]);
            document.getElementById('start-all-btn').disabled = allRunning;
            document.getElementById('stop-all-btn').disabled = !hasAnyRunning;
            const pauseBtn = document.getElementById('pause-all-btn');
            pauseBtn.disabled = !hasAnyRunning;
            if (allPaused) pauseBtn.innerHTML = '<svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>Resume All';
            else pauseBtn.innerHTML = '<svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>Pause All';
        }

        async function startAllWorkers() {
            const btn = document.getElementById('start-all-btn');
            const stoppedDrivers = allDrivers.filter(d => !workerStatus[d]);
            if (stoppedDrivers.length === 0) return;
            btn.disabled = true;
            btn.innerHTML = `${spinnerSvg} Starting All...`;
            for (const driver of stoppedDrivers) await startWorker(driver);
            btn.innerHTML = '<svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>Quick Start All';
            updateBulkButtons();
        }
        async function pauseAllWorkers() { const runningDrivers = Object.keys(workerStatus); for (const driver of runningDrivers) await togglePause(driver); }
        async function stopAllWorkers() {
            const btn = document.getElementById('stop-all-btn');
            const runningDrivers = Object.keys(workerStatus);
            if (runningDrivers.length === 0) return;
            if (!confirm('Stop all workers?')) return;
            btn.disabled = true;
            btn.innerHTML = `${spinnerSvg} Stopping All...`;
            for (const driver of runningDrivers) {
                try {
                    const response = await fetch('/worker/stop', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }, body: JSON.stringify({ driver }) });
                    const data = await response.json();
                    if (data.success) delete workerStatus[driver];
                } catch (error) { console.error(`Failed to stop ${driver}:`, error); }
            }
            await checkWorkerStatus();
            btn.innerHTML = '<svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" /></svg>Stop All';
            updateBulkButtons();
        }

        // Supervisor (legacy)
        async function checkSupervisorStatus() {
            try {
                const response = await fetch('/supervisor/status');
                const data = await response.json();
                updateSupervisorUI(data.running, data);
            } catch (error) { console.error('Failed to check supervisor status:', error); }
        }

        function updateSupervisorUI(running, data) {
            const statusIndicator = document.getElementById('supervisor-status-indicator');
            if (!statusIndicator) return;
        }

        // Initial load
        checkDrivers();
        loadBatches();
        loadQueuePauseStatus();
        setInterval(checkDrivers, 30000);
        loadSavedTab();
    </script>
</body>
</html>
