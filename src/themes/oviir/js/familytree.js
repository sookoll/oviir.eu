class FamilyTree {
  constructor(mapid) {
    this.size = [120, 70]
    this.buffer = {
      x: 12,
      y: 20
    }
    this.data = []
    this.map = L.map(mapid, {
      zoomControl: false,
      crs: L.CRS.Simple,
      minZoom: 1,
      maxZoom: 4
    }).setView([0, 0], 3)

    L.control.zoom({ position: 'bottomright' }).addTo(this.map)

    /*this.map.addEventListener('mousemove', e => {
      $('.coords').html(`${e.latlng.lat}, ${e.latlng.lng}`)
    })*/
  }
  load(data) {
    this.data = data
    // get first ancestor 0
    this.get(0).forEach(item => {
      item._generation = 0
      item._coords = [0, 0]
      this.create(item)
    })
  }
  get(id, type) {
    const relations = this.data.filter(item => item.bound_with === id)
    return type ? relations.filter(item => item.bound_is === type) : relations
  }
  create(ancestor) {
    const partners = this.get(ancestor.id, 'partner').map(item => `${item.firstname} ${item.lastname}`)
    const childrens = this.get(ancestor.id, 'child')
    const icon = L.divIcon({
      className: 'div-icon',
      html: `<div class="box"><b>${ancestor.id}. ${ancestor.firstname} ${ancestor.lastname}</b><br>${partners.join('<br>')}</div>`,
      iconSize: this.size,
      iconAnchor: [this.size[0] / 2, this.size[1] / 2]
    });
    L.marker(ancestor._coords, { icon: icon }).addTo(this.map)
    let childCount = childrens.length
    ancestor._childCount = childCount
    const generation = childCount ?
      ancestor._generation : ancestor._generation + 1
    const maxCount = Math.max(...this.getMaxCount(ancestor, ancestor._generation))
    const width = this.buffer.y * (maxCount - 1) / (ancestor._childCount - 1)
    console.log(maxCount, width)
    childrens.forEach(item => {
      item._generation = generation
      childCount--
      const x = ancestor._coords[0] - this.buffer.x
      const y = ancestor._childCount > 1 ?
        (ancestor._coords[1] + childCount * width) - width * (ancestor._childCount - 1) / 2:
        ancestor._coords[1] + childCount * this.buffer.y
      item._coords = [x, y]
      this.createChildLine(ancestor._coords, item._coords)
      this.create(item)
    })
  }
  add(data) {
    data.id = Number(data.id)
    data.bound_with = Number(data.bound_with)
    this.data.push(data)
  }
  reload() {
    this.map.eachLayer(layer => {
      this.map.removeLayer(layer)
    })
    this.load(this.data)
  }
  createChildLine(p1, p2) {
    const points = []
    points.push(p1)
    if (p1[1] !== p2[1]) {
      points.push([p1[0] - this.buffer.x / 2, p1[1]])
      points.push([p1[0] - this.buffer.x / 2, p2[1]])
    }
    points.push(p2)
    L.polyline(points, {
      color: 'black',
      weight: 1,
      smoothFactor: 1
    }).addTo(this.map)
  }
  getMaxCount(ancestor, generation = 0, results = []) {
    const childs = this.get(ancestor.id, 'child')
    if (!results[generation]) {
      results[generation] = childs.length
    } else {
      results[generation] += childs.length
    }
    if (childs.length) {
      generation += 1
      childs.forEach(item => {
        results = this.getMaxCount(item, generation, results)
      })
    }
    return results
  }
}
