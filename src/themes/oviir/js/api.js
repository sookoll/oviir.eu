/*
* Miuview client
*
* Creator: Mihkel Oviir
* 11.2019
*
* dependencies:
* 1. fetch
*/
function Api (options) {
  if (!options.endpoint) {
    return false
  }
  const defaults = {
    endpoint: null,
    hash: ''
  }
  this.options = Object.assign(defaults, options)
}

Api.prototype.load = function (options) {
  if (typeof options === 'undefined') {
    options = ''
  }
  return new Promise((resolve, reject) => {
    const params = typeof options.params === 'object' ?
      new URLSearchParams(params).toString() : options.params
    const url = params ? `${this.options.endpoint}?${params}` : `${this.options.endpoint}`
    fetch(url, {
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Basic ${this.options.hash}`
      }
    })
      .then(response => response.json())
      .then(response => resolve(response.records))
      .catch(reject)
  })
}

Api.prototype.insert = function (data) {
  return new Promise((resolve, reject) => {
    fetch(`${this.options.endpoint}`, {
      method: 'POST',
      body: JSON.stringify(data),
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Basic ${this.options.hash}`
      }
    })
      .then(response => response.text())
      .then(resolve)
      .catch(reject)
  })
}

Api.prototype.update = function (primary, data) {
  return new Promise((resolve, reject) => {
    fetch(`${this.options.endpoint}/${primary}`, {
      method: 'PUT',
      body: JSON.stringify(data),
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Basic ${this.options.hash}`
      }
    })
      .then(response => response.text())
      .then(resolve)
      .catch(reject)
  })
}
