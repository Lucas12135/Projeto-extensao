(() => {
  const editModal = document.getElementById("profile-edit-modal");
  const editTitle = document.getElementById("profile-edit-modal-title");
  const editControlContainer = document.getElementById("profile-edit-control");
  const editSaveButton = document.getElementById("profile-edit-save");

  if (
    !(editModal instanceof HTMLDialogElement) ||
    !(editTitle instanceof HTMLHeadingElement) ||
    !(editControlContainer instanceof HTMLElement) ||
    !(editSaveButton instanceof HTMLButtonElement)
  ) {
    throw new Error("Não foi possível inicializar a edição do perfil.");
  }

  let editingForm;
  let originalControl;
  let editTrigger;
  let modalControl;

  document.querySelectorAll(".profile-field").forEach((form) => {
    const control = form.querySelector('input[name="valor"], textarea[name="valor"]');
    const editButton = form.querySelector("[data-profile-edit]");
    const label = form.querySelector(".profile-field-label");

    if (
      !(control instanceof HTMLInputElement || control instanceof HTMLTextAreaElement) ||
      !(editButton instanceof HTMLButtonElement) ||
      !(label instanceof HTMLLabelElement)
    ) {
      throw new Error("Não foi possível inicializar um campo do perfil.");
    }

    editButton.addEventListener("click", () => {
      editingForm = form;
      originalControl = control;
      editTrigger = editButton;
      editTitle.textContent = `Editar ${label.textContent.trim()}`;
      editControlContainer.replaceChildren();

      const modalLabel = document.createElement("label");
      modalLabel.className = "profile-modal-label";
      modalLabel.htmlFor = "profile-edit-value";
      modalLabel.textContent = label.textContent.trim();

      modalControl = control.cloneNode(false);
      modalControl.id = "profile-edit-value";
      modalControl.removeAttribute("name");
      modalControl.disabled = false;
      modalControl.className = "profile-modal-input";
      modalControl.value = control.value;

      editControlContainer.append(modalLabel, modalControl);
      editModal.showModal();
      modalControl.focus();
    });
  });

  editSaveButton.addEventListener("click", () => {
    if (
      !editingForm ||
      !(originalControl instanceof HTMLInputElement || originalControl instanceof HTMLTextAreaElement) ||
      !(modalControl instanceof HTMLInputElement || modalControl instanceof HTMLTextAreaElement)
    ) {
      throw new Error("Não foi possível salvar a alteração do perfil.");
    }

    if (!modalControl.reportValidity()) {
      return;
    }

    originalControl.value = modalControl.value;
    originalControl.disabled = false;
    const formToSubmit = editingForm;
    editModal.close();
    formToSubmit.requestSubmit();
  });

  const confirmationForms = [
    { formClass: "profile-logout-form", modalId: "profile-logout-modal" },
    { formClass: "profile-delete-form", modalId: "profile-delete-modal" },
  ];

  confirmationForms.forEach(({ formClass, modalId }) => {
    const form = document.querySelector(`.${formClass}`);
    const modal = document.getElementById(modalId);
    const confirmButton = modal?.querySelector(`[data-confirm-form="${formClass}"]`);
    const confirmationInput = formClass === "profile-delete-form"
      ? document.getElementById("profile-delete-confirmation")
      : null;

    if (
      !(form instanceof HTMLFormElement) ||
      !(modal instanceof HTMLDialogElement) ||
      !(confirmButton instanceof HTMLButtonElement) ||
      (confirmationInput !== null && !(confirmationInput instanceof HTMLInputElement))
    ) {
      throw new Error(`Não foi possível inicializar a confirmação: ${formClass}.`);
    }

    if (confirmationInput instanceof HTMLInputElement) {
      confirmationInput.addEventListener("input", () => {
        confirmButton.disabled = confirmationInput.value !== "CONFIRMAR";
      });
    }

    let confirmed = false;
    form.addEventListener("submit", (event) => {
      if (confirmed) {
        confirmed = false;
        return;
      }
      event.preventDefault();
      if (modal.open) {
        return;
      }
      if (confirmationInput instanceof HTMLInputElement) {
        confirmationInput.value = "";
        confirmButton.disabled = true;
      }
      modal.showModal();
      if (confirmationInput instanceof HTMLInputElement) {
        confirmationInput.focus();
      }
    });

    confirmButton.addEventListener("click", () => {
      if (
        confirmationInput instanceof HTMLInputElement &&
        confirmationInput.value !== "CONFIRMAR"
      ) {
        return;
      }
      confirmed = true;
      modal.close();
      form.requestSubmit();
    });
  });

  document.querySelectorAll("[data-profile-modal-close]").forEach((button) => {
    button.addEventListener("click", () => {
      const modal = button.closest("dialog");
      if (modal instanceof HTMLDialogElement) {
        modal.close();
      }
    });
  });

  document.querySelectorAll("dialog.profile-modal").forEach((modal) => {
    modal.addEventListener("click", (event) => {
      if (event.target === modal && modal instanceof HTMLDialogElement) {
        modal.close();
      }
    });
  });

  editModal.addEventListener("close", () => {
    editControlContainer.replaceChildren();
    if (editTrigger instanceof HTMLButtonElement) {
      editTrigger.focus();
    }
    editingForm = undefined;
    originalControl = undefined;
    editTrigger = undefined;
    modalControl = undefined;
  });
})();
