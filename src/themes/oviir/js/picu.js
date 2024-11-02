/*
* Miuview client
*
* Creator: Mihkel Oviir
* 11.2019
*
* dependencies:
* 1. fetch
*/

class Picu {
  url = null
  thumbSize = 'SQ150'
  imgSize = 'L1600'

  constructor(options) {
    this.url = options.url
    if (options.thumbSize) {
      this.thumbSize = options.thumbSize
    }
    if (options.imgSize) {
      this.imgSize = options.imgSize
    }
  }

  load() {
    return new Promise((resolve, reject) => {
      fetch(this.url, {
        mode: 'cors',
        cache: 'no-cache',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json'
        }
      })
        .then(response => response.json())
        .then(response => this.prepare(response))
        .then(resolve)
        .catch(reject)
    })
  }

  prepare(response) {
    return response.map(item => {
      const thSize = item.sizes[this.thumbSize]
      const picSize = item.sizes[this.imgSize]

      return {
        id: item.id,
        title: item.title,
        description: item.description,
        thumb_url: thSize.url,
        img_url: picSize.url,
        img_width: picSize.width,
        img_height: picSize.height,
        datetaken: item.datetaken,
      }
    })
  }
}
