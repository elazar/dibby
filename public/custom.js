const lockButtonOnSubmit = (id, text) => {
  const button = document.getElementById(id)
  button.form.addEventListener("submit", () => {
    button.innerText = text
    button.disabled = true
    if (button.ariaBusy !== undefined) {
      button.ariaBusy = true
    } else {
      button.setAttribute("aria-busy", true)
    }
  })
}
