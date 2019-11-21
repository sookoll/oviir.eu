---
Title: Kontaktid
Description: Oviir.eu suguvõsa veebileht
Date: 1964-07-01
Image: http://oviir.eu/miuview-api?request=getimage&album=kokkutulekud&item=1964-1.-kokkutulek-tallinnas-linnu-teel-nurkade-juures-vol2.jpg&size=800&mode=longest
Thumbnail: http://oviir.eu/miuview-api?request=getimage&album=kokkutulekud&item=1964-1.-kokkutulek-tallinnas-linnu-teel-nurkade-juures-vol2.jpg&size=600&mode=square
Template: table
Category: suguvõsa
Api:
  source: persons
  params: include=id,firstname,lastname,address,email,phone,active&filter=deleted,eq,0
  countParams: include=id&filter=deleted,eq,0
  primaryColumn: id
  deleteColumn: deleted
Table:
  copyColumn: email
  columns:
    id:
      searchable: false
    firstname:
      title: Eesnimi
    lastname:
      title: Perenimi
    address:
      title: Aadress
      orderable: false
    email:
      title: E-post
      orderable: false
    phone:
      title: Telefon
      orderable: false
    active:
      title: Kutse
      searchable: false
      dataType: boolean
  editable: true
---
