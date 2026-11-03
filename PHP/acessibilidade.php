<div class="accessibility-widget">
    <section class="accessibility-panel" id="accessibility-panel" aria-labelledby="accessibility-title" hidden>
        <h2 id="accessibility-title">Opções de acessibilidade</h2>
        <div class="accessibility-controls">
            <div class="accessibility-slider-option">
                <label class="accessibility-slider-label" for="accessibility-text-scale">
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M2 5h10v2H8v12H6V7H2V5Zm13 5h7v2h-2.5v7h-2v-7H15v-2Z"/></svg>
                    <span>Texto maior</span>
                    <output id="accessibility-text-scale-value" for="accessibility-text-scale">100%</output>
                </label>
                <input id="accessibility-text-scale" class="accessibility-slider" data-setting="textScale" type="range" min="100" max="200" step="5" value="100">
            </div>
            <div class="accessibility-slider-option">
                <label class="accessibility-slider-label" for="accessibility-text-spacing">
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M3 5h18v2H3V5Zm3 6h12v2H6v-2Zm3 6h6v2H9v-2Z"/></svg>
                    <span>Espaçamento de texto</span>
                    <output id="accessibility-text-spacing-value" for="accessibility-text-spacing">0.00 em</output>
                </label>
                <input id="accessibility-text-spacing" class="accessibility-slider" data-setting="textSpacing" type="range" min="0" max="0.3" step="0.02" value="0">
            </div>
            <div class="accessibility-slider-option">
                <label class="accessibility-slider-label" for="accessibility-line-height">
                    <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 3 1 6h2v12H1l3 3 3-3H5V6h2L4 3Zm7 2h12v2H11V5Zm0 6h12v2H11v-2Zm0 6h12v2H11v-2Z"/></svg>
                    <span>Altura da linha</span>
                    <output id="accessibility-line-height-value" for="accessibility-line-height">1.6</output>
                </label>
                <input id="accessibility-line-height" class="accessibility-slider" data-setting="lineHeight" type="range" min="1.2" max="3" step="0.1" value="1.6">
            </div>
            <button type="button" class="accessibility-option" data-feature="contrast" aria-pressed="false">
                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20V2Zm0 0v20a10 10 0 0 0 0-20Z"/></svg>
                <span>Contraste +</span>
            </button>
            <button type="button" class="accessibility-option" data-feature="links" aria-pressed="false">
                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M10 13a1 1 0 0 1 0-2h4a1 1 0 1 1 0 2h-4Zm-2 4H6a5 5 0 0 1 0-10h5a1 1 0 1 1 0 2H6a3 3 0 0 0 0 6h2a1 1 0 1 1 0 2Zm5-9a1 1 0 0 1 1-1h4a5 5 0 0 1 0 10h-5a1 1 0 1 1 0-2h5a3 3 0 0 0 0-6h-4a1 1 0 0 1-1-1Z"/></svg>
                <span>Links destacados</span>
            </button>
            <button type="button" class="accessibility-option" data-feature="stop-animations" aria-pressed="false">
                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M7 5v14l12-7L7 5Zm-2-2h2v18H5V3Z"/></svg>
                <span>Parar animações</span>
            </button>
            <button type="button" class="accessibility-option" data-feature="hide-images" aria-pressed="false">
                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M3 3h18v18H3V3Zm2 2v10l4-4 4 4 2-2 4 4V5H5Zm13 14-5-5-2 2-4-4-2 2v5h13ZM7 9a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z"/></svg>
                <span>Ocultar imagens</span>
            </button>
            <button type="button" class="accessibility-option" data-feature="dyslexia-font" aria-pressed="false">
                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M3 5h18v2H3V5Zm0 4h12v2H3V9Zm0 4h18v2H3v-2Zm0 4h12v2H3v-2Z"/></svg>
                <span>Dislexia amigável</span>
            </button>
            <button type="button" class="accessibility-option" data-feature="large-cursor" aria-pressed="false">
                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="m5 2 14 12-6 1-3 6L5 2Zm3.2 5.2 2.8 9.2 1.1-2.3 2.4-.4-6.3-6.5Z"/></svg>
                <span>Cursor</span>
            </button>
            <button type="button" class="accessibility-option" data-feature="text-alignment" aria-pressed="false" aria-label="Alinhamento de texto: padrão">
                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M3 4h18v2H3V4Zm0 4h12v2H3V8Zm0 4h18v2H3v-2Zm0 4h12v2H3v-2Zm0 4h18v2H3v-2Z"/></svg>
                <span>Alinhamento de texto</span>
            </button>
            <button type="button" class="accessibility-option" data-feature="libras" aria-label="Abrir tradução em Libras com o VLibras">
                <svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm-1 5a1 1 0 0 0-1 1v5.5l-1.6-2.1a1 1 0 0 0-1.6 1.2l3.5 4.7A3 3 0 0 0 12.7 18H15a3 3 0 0 0 3-3v-2a1 1 0 1 0-2 0v2a1 1 0 0 1-1 1h-2.3a1 1 0 0 1-.8-.4l-1.9-2.5V8a1 1 0 0 0-1-1Zm-5.8-.7a1 1 0 0 0-1.4 1.4A11 11 0 0 0 2 12a1 1 0 0 0 2 0 9 9 0 0 1 1.2-4.3ZM18.8 7A9 9 0 0 1 20 12a1 1 0 0 0 2 0 11 11 0 0 0-2.4-6.9A1 1 0 0 0 18.8 7ZM6.3 4.2A1 1 0 0 0 5 5.8 9 9 0 0 1 7 12a1 1 0 0 0 2 0 11 11 0 0 0-2.7-7.8Zm11.4 0A11 11 0 0 0 15 12a1 1 0 0 0 2 0 9 9 0 0 1 2-6.2 1 1 0 0 0-1.3-1.6Z"/></svg>
                <span>Libras (VLibras)</span>
            </button>
        </div>
        <div class="accessibility-panel-footer">
            <p class="accessibility-status" id="accessibility-status" aria-live="polite">Selecione uma opção para personalizar a página.</p>
            <button type="button" class="accessibility-reset" id="reset-accessibility">Restaurar padrão</button>
        </div>
    </section>
    <button
        type="button"
        class="accessibility-toggle"
        id="accessibility-toggle"
        aria-controls="accessibility-panel"
        aria-expanded="false"
    >
        <svg aria-hidden="true" viewBox="0 0 24 24" focusable="false">
            <path d="M12 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm7 6H5a1 1 0 0 0 0 2h5v11a1 1 0 0 0 2 0v-6h1v6a1 1 0 0 0 2 0V10h4a1 1 0 0 0 0-2Z"/>
        </svg>
    </button>
</div>

<script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
<script>
    new window.VLibras.Widget('https://vlibras.gov.br/app');
</script>
<script src="JS/acessibilidade.js"></script>