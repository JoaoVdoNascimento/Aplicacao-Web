</main>

    <div id="confirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white p-6 rounded-lg shadow-xl max-w-sm w-full mx-4">
            <h3 class="text-xl font-bold mb-4">Confirmar Exclusão</h3>
            <p class="mb-6">Tem certeza de que deseja excluir este registro?</p>
            <div class="flex justify-end space-x-4">
                <button id="cancelDelete" class="px-4 py-2 rounded-md bg-gray-200 text-gray-800 hover:bg-gray-300">Cancelar</button>
                <button id="confirmDelete" class="px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700">Excluir</button>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const API_URL = 'api.php';
        const PDF_ATTENDANCES_REPORT_URL = 'report_pdf.php';
        const PDF_CLIENTS_REPORT_URL = 'report_clientes_pdf.php';

        const contentPanel = document.getElementById('contentPanel');
        const loadingSpinner = document.getElementById('loading');
        const navButtons = document.querySelectorAll('.nav-button');
        const confirmModal = document.getElementById('confirmModal');
        const confirmDeleteBtn = document.getElementById('confirmDelete');
        const cancelDeleteBtn = document.getElementById('cancelDelete');

        let currentDeleteEntity = null;
        let currentDeleteId = null;

        // --- FUNÇÕES AUXILIARES ---
        function formatarDataBR(dataString) {
            if (!dataString || typeof dataString !== 'string' || dataString.substring(0, 4) === '0000') return '';
            const parts = dataString.split('-');
            if (parts.length !== 3) return dataString;
            const [ano, mes, dia] = parts;
            return `${dia}/${mes}/${ano}`;
        }

        function showLoading() { loadingSpinner.classList.remove('hidden'); contentPanel.classList.add('hidden'); }
        function hideLoading() { loadingSpinner.classList.add('hidden'); contentPanel.classList.remove('hidden'); }

        function showConfirmModal(entity, id) {
            currentDeleteEntity = entity;
            currentDeleteId = id;
            confirmModal.classList.remove('hidden');
        }

        function hideConfirmModal() {
            confirmModal.classList.add('hidden');
            currentDeleteEntity = null;
            currentDeleteId = null;
        }

        async function deleteRecord(entity, id) {
            try {
                const response = await fetch(`${API_URL}/${entity}/${id}`, { method: 'DELETE' });
                if (response.ok) {
                    loadContent(entity);
                } else {
                    const result = await response.json(); console.error("Erro ao excluir:", result.message);
                }
            } catch (error) {
                console.error("Erro na requisição de exclusão:", error);
            }
        }
        
        async function handleDownloadAttendancesCsvReport() {
            try {
                const response = await fetch(`${API_URL}/atendimentos`);
                const data = await response.json();
                const headers = ["Cliente", "Nome do Pet", "Atendente", "Serviço", "Valor (R$)", "Data", "Hora"];
                const rows = data.map(row => {
                    const displayDate = formatarDataBR(row.dataAtendimento);
                    const displayTime = row.horaAtendimento ? row.horaAtendimento.substring(0, 5) : '';
                    const values = [row.cliente_nome, row.cliente_nome_pet, row.atendente_nome, row.tipoServico, parseFloat(row.valor).toFixed(2), displayDate, displayTime];
                    return values.map(v => `"${v}"`).join(',');
                });
                const csvContent = [headers.join(","), ...rows].join("\n");
                const blob = new Blob([`\uFEFF${csvContent}`], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.setAttribute('href', url);
                link.setAttribute('download', 'relatorio_atendimentos.csv');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } catch (error) { console.error("Erro ao baixar relatório de atendimentos:", error); }
        }

        async function handleDownloadClientsCsvReport() {
            try {
                const response = await fetch(`${API_URL}/clientes`);
                const data = await response.json();
                const headers = ["ID", "Nome", "Telefone", "Email", "Nome do Pet", "Tipo do Pet"];
                const rows = data.map(row => {
                    const values = [row.id, row.nome, row.telefone, row.email, row.nomePet, row.tipoPet];
                    return values.map(v => `"${v || ''}"`).join(',');
                });
                const csvContent = [headers.join(","), ...rows].join("\n");
                const blob = new Blob([`\uFEFF${csvContent}`], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.setAttribute('href', url);
                link.setAttribute('download', 'relatorio_clientes.csv');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } catch (error) { console.error("Erro ao baixar relatório de clientes:", error); }
        }

        function handleDownloadClientsPdfReport() {
            window.open(PDF_CLIENTS_REPORT_URL, '_blank');
        }

        function handleDownloadAttendancesPdfReport() {
            window.open(PDF_ATTENDANCES_REPORT_URL, '_blank');
        }

        // --- LÓGICA PRINCIPAL ---
        async function loadContent(tab) {
            showLoading();
            let htmlContent = '';
            
            try {
                if (tab === 'clientes') {
                    const response = await fetch(`${API_URL}/clientes`);
                    const data = await response.json();
                    htmlContent = `<h2 class="text-2xl font-bold mb-4 text-gray-800">Cadastro de Clientes</h2><form id="formClientes" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8"><div><label for="nomeCliente" class="block text-sm font-medium text-gray-700">Nome:</label><input type="text" id="nomeCliente" name="nome" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2" required></div><div><label for="telefoneCliente" class="block text-sm font-medium text-gray-700">Telefone:</label><input type="tel" id="telefoneCliente" name="telefone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2" required></div><div><label for="emailCliente" class="block text-sm font-medium text-gray-700">Email:</label><input type="email" id="emailCliente" name="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2"></div><div><label for="nomePet" class="block text-sm font-medium text-gray-700">Nome do Pet:</label><input type="text" id="nomePet" name="nomePet" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2" required></div><div><label for="tipoPet" class="block text-sm font-medium text-gray-700">Tipo do Pet:</label><select id="tipoPet" name="tipoPet" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 bg-white" required><option value="cachorro">Cachorro</option><option value="gato">Gato</option><option value="outros">Outros</option></select></div><div class="md:col-span-2"><button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">Cadastrar Cliente</button></div></form><h3 class="text-xl font-semibold mb-3 text-gray-800">Clientes Cadastrados</h3><div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200"><thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telefone</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome do Pet</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo do Pet</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th></tr></thead><tbody class="bg-white divide-y divide-gray-200">${data.map(cliente => `<tr><td class="px-6 py-4">${cliente.nome}</td><td class="px-6 py-4">${cliente.telefone}</td><td class="px-6 py-4">${cliente.email || ''}</td><td class="px-6 py-4">${cliente.nomePet}</td><td class="px-6 py-4">${cliente.tipoPet}</td><td class="px-6 py-4"><button data-action="delete" data-entity="clientes" data-id="${cliente.id}" class="text-red-600 hover:text-red-900">Excluir</button></td></tr>`).join('')}</tbody></table></div>`;
                
                } else if (tab === 'atendentes') {
                    const response = await fetch(`${API_URL}/atendentes`);
                    const data = await response.json();
                    htmlContent = `<h2 class="text-2xl font-bold mb-4 text-gray-800">Cadastro de Atendentes</h2><form id="formAtendentes" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8"><div><label for="nomeAtendente" class="block text-sm font-medium text-gray-700">Nome:</label><input type="text" id="nomeAtendente" name="nome" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2" required></div><div><label for="telefoneAtendente" class="block text-sm font-medium text-gray-700">Telefone:</label><input type="tel" id="telefoneAtendente" name="telefone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2" required></div><div class="md:col-span-2"><label for="emailAtendente" class="block text-sm font-medium text-gray-700">Email:</label><input type="email" id="emailAtendente" name="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2"></div><div class="md:col-span-2"><button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">Cadastrar Atendente</button></div></form><h3 class="text-xl font-semibold mb-3 text-gray-800">Atendentes Cadastrados</h3><div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200"><thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telefone</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th></tr></thead><tbody class="bg-white divide-y divide-gray-200">${data.map(atendente => `<tr><td class="px-6 py-4">${atendente.nome}</td><td class="px-6 py-4">${atendente.telefone}</td><td class="px-6 py-4">${atendente.email || ''}</td><td class="px-6 py-4"><button data-action="delete" data-entity="atendentes" data-id="${atendente.id}" class="text-red-600 hover:text-red-900">Excluir</button></td></tr>`).join('')}</tbody></table></div>`;

                } else if (tab === 'atendimentos') {
                    const [clientesRes, atendentesRes, atendimentosRes] = await Promise.all([fetch(`${API_URL}/clientes`), fetch(`${API_URL}/atendentes`), fetch(`${API_URL}/atendimentos`)]);
                    const clientesData = await clientesRes.json();
                    const atendentesData = await atendentesRes.json();
                    const atendimentosData = await atendimentosRes.json();
                    const serviceValues = {'banho-e-tosa': 80.00, 'banho': 50.00, 'tosa': 40.00};
                    htmlContent = `<h2 class="text-2xl font-bold mb-4 text-gray-800">Registro de Atendimentos</h2><form id="formAtendimentos" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8"><div><label for="clienteAtendimento" class="block text-sm font-medium text-gray-700">Cliente:</label><select id="clienteAtendimento" name="clienteId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 bg-white" required><option value="">Selecione um Cliente</option>${clientesData.map(c => `<option value="${c.id}">${c.nome} (${c.nomePet})</option>`).join('')}</select></div><div><label for="atendenteAtendimento" class="block text-sm font-medium text-gray-700">Atendente:</label><select id="atendenteAtendimento" name="atendenteId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 bg-white" required><option value="">Selecione um Atendente</option>${atendentesData.map(a => `<option value="${a.id}">${a.nome}</option>`).join('')}</select></div><div><label for="tipoServico" class="block text-sm font-medium text-gray-700">Tipo de Serviço:</label><select id="tipoServico" name="tipoServico" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 bg-white" required><option value="banho-e-tosa">Banho e Tosa</option><option value="banho">Banho</option><option value="tosa">Tosa</option></select></div><div><label for="valorServico" class="block text-sm font-medium text-gray-700">Valor (R$):</label><input type="number" id="valorServico" name="valor" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2" step="0.01" required value="${serviceValues['banho-e-tosa'].toFixed(2)}"></div><div><label for="dataAtendimento" class="block text-sm font-medium text-gray-700">Data:</label><input type="date" id="dataAtendimento" name="dataAtendimento" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2" required></div><div><label for="horaAtendimento" class="block text-sm font-medium text-gray-700">Hora:</label><input type="time" id="horaAtendimento" name="horaAtendimento" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2" required></div><div class="md:col-span-2"><button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">Registrar Atendimento</button></div></form><h3 class="text-xl font-semibold mb-3 text-gray-800">Atendimentos Registrados</h3><div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200"><thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Atendente</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Serviço</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hora</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th></tr></thead><tbody class="bg-white divide-y divide-gray-200">${atendimentosData.map(atendimento => `<tr><td class="px-6 py-4">${atendimento.cliente_nome} (${atendimento.cliente_nome_pet})</td><td class="px-6 py-4">${atendimento.atendente_nome}</td><td class="px-6 py-4">${atendimento.tipoServico}</td><td class="px-6 py-4">${parseFloat(atendimento.valor).toFixed(2)}</td><td class="px-6 py-4">${formatarDataBR(atendimento.dataAtendimento)}</td><td class="px-6 py-4">${atendimento.horaAtendimento ? atendimento.horaAtendimento.substring(0,5) : ''}</td><td class="px-6 py-4"><button data-action="delete" data-entity="atendimentos" data-id="${atendimento.id}" class="text-red-600 hover:text-red-900">Excluir</button></td></tr>`).join('')}</tbody></table></div>`;

                } else if (tab === 'relatorios') {
                    const response = await fetch(`${API_URL}/atendimentos`);
                    const data = await response.json();
                    htmlContent = `<h2 class="text-2xl font-bold mb-4 text-gray-800">Relatórios</h2><div class="flex flex-wrap gap-4 mb-6"><button id="downloadClientsPdfReport" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">Baixar Relatório de Clientes (PDF)</button><button id="downloadClientsCsvReport" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">Baixar Relatório de Clientes (CSV)</button><button id="downloadAttendancesPdfReport" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">Baixar Relatório de Atendimentos (PDF)</button><button id="downloadAttendancesCsvReport" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">Baixar Relatório de Atendimentos (CSV)</button></div><h3 class="text-xl font-semibold mb-3 text-gray-800">Visualização de Atendimentos Recentes</h3><div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200"><thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome do Pet</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Atendente</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Serviço</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor (R$)</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th><th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hora</th></tr></thead><tbody class="bg-white divide-y divide-gray-200">${data.map(atendimento => `<tr><td class="px-6 py-4">${atendimento.cliente_nome}</td><td class="px-6 py-4">${atendimento.cliente_nome_pet}</td><td class="px-6 py-4">${atendimento.atendente_nome}</td><td class="px-6 py-4">${atendimento.tipoServico}</td><td class="px-6 py-4">${parseFloat(atendimento.valor).toFixed(2)}</td><td class="px-6 py-4">${formatarDataBR(atendimento.dataAtendimento)}</td><td class="px-6 py-4">${atendimento.horaAtendimento ? atendimento.horaAtendimento.substring(0,5) : ''}</td></tr>`).join('')}</tbody></table></div>`;
                }
            } catch (error) {
                console.error("Erro ao carregar dados:", error);
                htmlContent = `<p class="text-red-500">Erro ao carregar conteúdo.</p>`;
            } finally {
                contentPanel.innerHTML = htmlContent;
                attachEventListeners(tab);
                hideLoading();
            }
        }
        
        // ***** FUNÇÃO ATUALIZADA *****
        function attachEventListeners(tab) {
            const formId = `form${tab.charAt(0).toUpperCase() + tab.slice(1)}`;
            const form = document.getElementById(formId);
            if (form) {
                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    handleFormSubmit(tab, form);
                });
            }

            // Lógica especial para a aba de atendimentos (atualização de preço)
            if (tab === 'atendimentos') {
                const tipoServicoSelect = document.getElementById('tipoServico');
                const valorServicoInput = document.getElementById('valorServico');
                if (tipoServicoSelect && valorServicoInput) {
                    tipoServicoSelect.addEventListener('change', () => {
                        const serviceValues = {
                            'banho-e-tosa': 80.00,
                            'banho': 50.00,
                            'tosa': 40.00,
                        };
                        valorServicoInput.value = serviceValues[tipoServicoSelect.value]?.toFixed(2) || '0.00';
                    });
                }
            } 
            // Lógica especial para a aba de relatórios (botões de download)
            else if (tab === 'relatorios') {
                document.getElementById('downloadAttendancesCsvReport')?.addEventListener('click', handleDownloadAttendancesCsvReport);
                document.getElementById('downloadAttendancesPdfReport')?.addEventListener('click', handleDownloadAttendancesPdfReport);
                document.getElementById('downloadClientsCsvReport')?.addEventListener('click', handleDownloadClientsCsvReport);
                document.getElementById('downloadClientsPdfReport')?.addEventListener('click', handleDownloadClientsPdfReport);
            }
        }
        
        async function handleFormSubmit(entity, form) {
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            if (entity === 'atendimentos') data.valor = parseFloat(data.valor);
            try {
                const response = await fetch(`${API_URL}/${entity}`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(data)
                });
                if (response.ok) {
                    form.reset();
                    loadContent(entity);
                } else {
                    const result = await response.json(); console.error("Erro ao salvar:", result.message);
                }
            } catch (error) {
                console.error("Erro na requisição:", error);
            }
        }
        
        // --- EVENT LISTENERS DE INICIALIZAÇÃO ---
        confirmDeleteBtn.addEventListener('click', () => {
            if (currentDeleteEntity && currentDeleteId) { deleteRecord(currentDeleteEntity, currentDeleteId); }
            hideConfirmModal();
        });
        cancelDeleteBtn.addEventListener('click', hideConfirmModal);
        
        contentPanel.addEventListener('click', (event) => {
            const button = event.target.closest('button[data-action="delete"]');
            if (button) {
                const entity = button.dataset.entity;
                const id = button.dataset.id;
                showConfirmModal(entity, id);
            }
        });

        navButtons.forEach(button => {
            button.addEventListener('click', (event) => {
                navButtons.forEach(btn => {
                    btn.classList.remove('bg-white', 'text-indigo-700', 'shadow-md');
                    btn.classList.add('text-white', 'hover:bg-indigo-700');
                });
                const currentButton = event.currentTarget;
                currentButton.classList.remove('text-white', 'hover:bg-indigo-700');
                currentButton.classList.add('bg-white', 'text-indigo-700', 'shadow-md');
                const tabId = currentButton.id.replace('tab', '').toLowerCase();
                loadContent(tabId);
            });
        });

        // Carga inicial
        document.getElementById('tabClientes').click();
    });
    </script>
</body>
</html>