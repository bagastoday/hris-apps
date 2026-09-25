
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Git Test Pa</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#2563eb',
                    }
                }
            }
        }
    </script>
</head>

<body class="min-h-screen bg-slate-100">

    <!-- Navbar -->
    <nav class="bg-white border-b border-slate-200">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">

            <div class="font-bold text-xl text-slate-900">
                TestProject
            </div>

            <span class="text-sm text-slate-500">
                Git Testing
            </span>

        </div>
    </nav>


    <!-- Main Content -->
    <main class="max-w-6xl mx-auto px-6 py-16">

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 md:p-12">

            <div class="max-w-2xl">

                <span class="inline-flex items-center px-3 py-1 rounded-full
                    bg-blue-50 text-blue-600 text-sm font-medium mb-5">
                    Laravel Test Page
                </span>

                <h1 class="text-4xl md:text-5xl font-bold text-slate-900 mb-5">
                    Git Push Berhasil 🚀
                </h1>

                <p class="text-lg text-slate-600 leading-relaxed mb-8">
                    Halaman ini dibuat sebagai dummy page untuk melakukan
                    testing perubahan kode, commit, dan push ke repository Git.
                </p>


                <!-- Status -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">

                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-5">
                        <p class="text-sm text-slate-500 mb-2">
                            Environment
                        </p>

                        <p class="font-semibold text-slate-900">
                            Development
                        </p>
                    </div>


                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-5">
                        <p class="text-sm text-slate-500 mb-2">
                            Framework
                        </p>

                        <p class="font-semibold text-slate-900">
                            Laravel
                        </p>
                    </div>


                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-5">
                        <p class="text-sm text-slate-500 mb-2">
                            Status
                        </p>

                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 bg-green-500 rounded-full"></span>

                            <p class="font-semibold text-green-600">
                                Online
                            </p>
                        </div>
                    </div>

                </div>


                <!-- Button -->
                <div class="flex flex-wrap gap-3">

                    <button
                        onclick="showMessage()"
                        class="px-6 py-3 bg-primary text-white rounded-lg
                        font-medium hover:bg-blue-700 transition">
                        Test Button
                    </button>

                    <a href="/"
                        class="px-6 py-3 bg-white border border-slate-300
                        text-slate-700 rounded-lg font-medium
                        hover:bg-slate-50 transition">
                        Kembali
                    </a>

                </div>


                <!-- Message -->
                <div
                    id="message"
                    class="hidden mt-6 p-4 rounded-lg bg-green-50
                    border border-green-200 text-green-700">
                    ✅ JavaScript berhasil berjalan!
                </div>

            </div>

        </div>


        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-sm text-slate-400">
                Test page • TalentaCore • {{ date('Y') }}
            </p>
        </div>

    </main>


    <script>
        function showMessage() {
            const message = document.getElementById('message');

            message.classList.remove('hidden');
        }
    </script>

</body>
</html>
```
