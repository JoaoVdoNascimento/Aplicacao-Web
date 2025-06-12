<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Pet Shop </title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
            color: #1f2937;
        }
        /* Estilos básicos para garantir responsividade em tabelas */
        .overflow-x-auto {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 6px; /* Ajuste o padding para telas menores */
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        @media (max-width: 768px) {
            th, td {
                padding: 8px 4px;
                font-size: 0.75rem; /* Menor fonte em telas menores */
            }
            .nav-button {
                font-size: 0.875rem; /* Menor fonte para botões de navegação */
                padding: 0.5rem 0.75rem;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-gray-100 font-sans antialiased text-gray-900">
    <nav class="bg-gradient-to-r from-purple-600 to-indigo-600 shadow-lg p-4">
        <div class="container mx-auto flex flex-col md:flex-row justify-between items-center">
            <div class="text-white text-2xl font-bold mb-4 md:mb-0">Pet Shop </div>
            <ul class="flex space-x-2 md:space-x-4">
                <li>
                    <button id="tabClientes" class="nav-button py-2 px-4 rounded-md transition-colors duration-300 bg-white text-indigo-700 shadow-md">
                        Clientes
                    </button>
                </li>
                <li>
                    <button id="tabAtendentes" class="nav-button py-2 px-4 rounded-md transition-colors duration-300 text-white hover:bg-indigo-700">
                        Atendentes
                    </button>
                </li>
                <li>
                    <button id="tabAtendimentos" class="nav-button py-2 px-4 rounded-md transition-colors duration-300 text-white hover:bg-indigo-700">
                        Atendimento
                    </button>
                </li>
                <li>
                    <button id="tabRelatorios" class="nav-button py-2 px-4 rounded-md transition-colors duration-300 text-white hover:bg-indigo-700">
                        Relatório
                    </button>
                </li>
            </ul>
        </div>
    </nav>

    <main class="container mx-auto p-6">
