document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modalMoverPrateleira');
    const btnFechar = document.getElementById('btnFecharModalMover');
    const inputIdEstoque = document.getElementById('mover_id_estoque');
    const inputCaixasMover = document.getElementById('caixas_mover');
    const tituloModal = document.getElementById('modalTituloProduto');

    // Event Delegation: escuta o clique em qualquer botão de mover
    document.addEventListener('click', (e) => {
        if (e.target && e.target.classList.contains('btn-mover')) {
            const btn = e.target;
            const id = btn.getAttribute('data-id');
            const nome = btn.getAttribute('data-nome');
            const maxQtd = btn.getAttribute('data-max');

            if (modal && inputIdEstoque && inputCaixasMover) {
                inputIdEstoque.value = id;
                tituloModal.textContent = `Mover ${nome}`;
                inputCaixasMover.max = maxQtd;
                inputCaixasMover.value = '';
                modal.showModal();
            }
        }
    });

    // Botão de fechar modal
    if (btnFechar && modal) {
        btnFechar.addEventListener('click', () => {
            modal.close();
        });
    }

    // Fechar ao clicar fora do modal
    if (modal) {
        modal.addEventListener('click', (e) => {
            const rect = modal.getBoundingClientRect();
            if (
                e.clientX < rect.left || e.clientX > rect.right ||
                e.clientY < rect.top || e.clientY > rect.bottom
            ) {
                modal.close();
            }
        });
    }
});