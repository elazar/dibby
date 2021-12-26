const lockButtonOnSubmit = (id, text) => {
  const button = document.getElementById(id)
  button.form.addEventListener("submit", (evt) => {
    if (evt.target !== button) {
      return
    }
    button.innerText = text
    if (button.ariaBusy !== undefined) {
      button.ariaBusy = true
    } else {
      button.setAttribute("aria-busy", true)
    }
  })
}
