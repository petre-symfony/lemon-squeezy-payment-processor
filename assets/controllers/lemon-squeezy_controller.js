import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static values = {
    checkoutCreateUrl: String
  }

  connect() {}
  openOverlay(e){
    e.preventDefault()

    const linkEl = e.currentTarget

    fetch(this.checkoutCreateUrlValue, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      }
    })
      .then(response => {
        if(!response.ok) {
          throw new Error("Network response was not ok " + response.statusText)
        }

        return response.json()
      })
      .then(data => {
        window.LemonSqueezy.Url.Open(data.targetUrl)
      })
      .catch(error => {
        console.error('Fetch error: ', error)
      })
  }
}