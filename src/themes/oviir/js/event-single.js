(function($) {

  if (config.coords) {
    const coords = config.coords.split(',').map(c => Number(c.trim()))
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
    }).setView(coords, 6)

    new L.tileLayer('http://tiles.maaamet.ee/tm/tms/1.0.0/kaart/{z}/{x}/{y}.png', {
        maxZoom: crs.options.resolutions.length,
        minZoom: 2,
        continuousWorld: true,
        attribution: 'Aluskaart © <a href="http://maaamet.ee">Eesti Maa-amet</a>',
        tms: true
    }).addTo(map)

    new L.marker(coords).addTo(map);
  }

})(jQuery);
