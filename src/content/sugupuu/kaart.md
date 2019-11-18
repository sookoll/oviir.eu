---
Title: Sugupuu kaart
Description: Interaktiivne sugupuu
Image: http://oviir.eu/miuview-api?request=getimage&album=kokkutulekud&item=1975-12.-kokkutulek-karellide-juures.jpg&size=1200&mode=longest
Template: map
Category: suguvõsa
Api:
  source: persons
  params: exclude=deleted,modified&filter=deleted,eq,0
  primaryColumn: id
  deleteColumn: deleted
Table:
  columns:
    id:
      title: Id
    firstname:
      title: Eesnimi
      required: true
    lastname:
      title: Perenimi
    birth:
      title: Sünd
    death:
      title: Surm
    bound_with:
      title: Seotud sugulane
    bound_is:
      title: Seos
    ancestor:
      title: Haru
    address:
      title: Aadress
    email:
      title: E-post
    phone:
      title: Telefon
    active:
      title: Kutse
      dataType: boolean
---
