(function($, JSON) {

  if (config.locations) {
    const locations = JSON.parse(config.locations)
    const crs = new L.Proj.CRS(
      'EPSG:3301',
      '+proj=lcc +lat_1=59.33333333333334 +lat_2=58 +lat_0=57.51755393055556 +lon_0=24 +x_0=500000 +y_0=6375000 +ellps=GRS80 +towgs84=0,0,0,0,0,0,0 +units=m +no_defs',
      {
        origin: [40500, 5993000],
        resolutions: [
          4000.0, 2000.0, 1000.0, 500.0, 250.0, 125.0, 62.5, 31.25, 15.625, 7.8125, 3.90625,
          1.953125, 0.9765625, 0.48828125, 0.244140625, 0.122070313, 0.061035157],
        bounds: L.bounds([[40500, 5993000], [1064500, 7017000]])
      }
    )
    const map = L.map('map', {
      crs: crs
    }).setView([58.67212, 25.64129], 3)

    new L.tileLayer('http://tiles.maaamet.ee/tm/tms/1.0.0/kaart/{z}/{x}/{y}.png', {
        maxZoom: crs.options.resolutions.length,
        minZoom: 2,
        continuousWorld: true,
        attribution: 'Aluskaart © <a href="http://maaamet.ee">Eesti Maa-amet</a>',
        tms: true
    }).addTo(map)

    const markers = locations.map(place => {
      const coords = place.coords.split(',').map(c => Number(c.trim()))
      return new L.marker(coords).bindPopup(`<a href="${place.url}">${place.label}</a>`)
    })

    const group = L.featureGroup(markers).addTo(map)

    $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
      map.invalidateSize()
    })
  }

})(jQuery, JSON)
