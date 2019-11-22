/*
* Miuview client
*
* Creator: Mihkel Oviir
* 11.2019
*
* dependencies:
* 1. fetch
*/

function MiuView (options) {
  const defaults = {
    request: 'getalbum',
    title: '',
    album: '*',
    item: '*',
    key: '',
    thsize: 150,
    size: 1200,
    start: 0,
    limit: ''
  }
  this.options = Object.assign(defaults, options)
}

MiuView.prototype.load = function () {
  let params = {
    request: this.options.request,
    album: this.options.album,
    thsize: this.options.thsize,
    key: this.options.key
  }
  if (this.options.request === 'getitem') {
    params = Object.assign(params, {
      item: this.options.item,
      size: this.options.size,
      start: this.options.start,
      limit: this.options.limit
    })
  }
  return new Promise((resolve, reject) => {
    // FIXME: remove
    params.api = 'miuview'
    // FIXME & -> ?
    fetch(this.options.url + encodeURI('?' + new URLSearchParams(params).toString()), {
      mode: 'cors',
      cache: 'no-cache',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json'
      }
    })
      .then(response => response.json())
      .then(resolve)
      .catch(reject)
  })
}
