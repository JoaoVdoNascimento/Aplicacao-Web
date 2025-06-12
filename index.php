<?php
// Inclui o cabeçalho da página
include 'header.php';
?>

        <div id="loading" class="flex justify-center items-center h-48 hidden">
            <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-indigo-500"></div>
            <p class="ml-4 text-gray-700">Carregando...</p>
        </div>

        <div id="contentPanel" class="p-4 bg-white rounded-lg shadow-md">
            <!-- Conteúdo dinâmico será carregado aqui pelo JavaScript -->
        </div>

<?php
// Inclui o rodapé da página
include 'footer.php';
?>
