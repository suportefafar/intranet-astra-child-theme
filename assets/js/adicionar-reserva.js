/*
 * Adiciona um listener para um evento despachado no formulário
 * de adição de reservas, no CF7, quando a submissão é feita com sucesso
 */
document.addEventListener("onAddEventSuccess", () => {
  window.history.back();
});
