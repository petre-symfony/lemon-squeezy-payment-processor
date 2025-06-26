import { Controler } from '@hotwired/stimulus'

export default class extends Controler {
  static values = {
    checkoutCreateUrl: String
  }

  connect() {}
  openOverlay(){}
}