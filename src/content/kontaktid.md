---
Title: Kontaktid
Description: Oviir.eu suguvõsa veebileht
Date: 1964-07-01
Image: http://oviir.eu/miuview-api?request=getimage&album=kokkutulekud&item=1964-1.-kokkutulek-tallinnas-linnu-teel-nurkade-juures-vol2.jpg&size=800&mode=longest
Thumbnail: http://oviir.eu/miuview-api?request=getimage&album=kokkutulekud&item=1964-1.-kokkutulek-tallinnas-linnu-teel-nurkade-juures-vol2.jpg&size=600&mode=square
Template: table
Category: suguvõsa
Edit: admins
Api:
  source: persons
  params: exclude=deleted,modified&filter=deleted,eq,0&filter=death,is,null&order=firstname
  countParams: include=id&filter=deleted,eq,0
  primaryColumn: id
  deleteColumn: deleted
Table:
  copyColumn: email
  columns:
    id:
      searchable: false
      visible: true
    firstname:
      title: Eesnimi
      required: true
      visible: true
    lastname:
      title: Perenimi
      visible: true
    birth:
      title: Sünd
      visible: true
    death:
      title: Surm
    bound_with:
      title: Seotud sugulane
      dataType: 'search'
    bound_is:
      title: Seos
      dataType: select
      select:
        null: -- Vali --
        child: Järglane
        partner: Kaaslane
    ancestor:
      title: Haru
    address:
      title: Aadress
      orderable: false
      visible: true
    email:
      title: E-post
      orderable: false
      visible: true
    phone:
      title: Telefon
      orderable: false
      visible: true
    active:
      title: Kutse
      searchable: false
      dataType: boolean
      visible: true
  editable: true
---
