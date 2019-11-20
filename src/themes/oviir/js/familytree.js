class FamilyTree {
  constructor(options) {
    this.el = d3.select(options.target)
    this.nameFormat = options.nameFormat
    this.padding = {
      y: 100,
      x: 2
    }
    this.viewer = {
      width: this.el.node().getBoundingClientRect().width,
      height: this.el.node().getBoundingClientRect().height
    }
    this.zoomListener = d3.behavior.zoom().scaleExtent([0.1, 3]).on('zoom', () => {
      this.zoom()
    })
    this.tree = d3.layout.tree()
      .size([this.viewer.height, this.viewer.width])
    this.svg = null
    this.diagonal = d3.svg.diagonal()
      .projection(d => [d.x, d.y])
    this.svgGroup = null
    this.rect = null
    this.panSpeed = 200
    this.selectedNode = null
    this.duration = 750
    this.data = {}
    // Redraw based on the new size whenever the browser window is resized.
    window.addEventListener("resize", () => {
      this.viewer = {
        width: this.el.node().getBoundingClientRect().width * 2,
        height: this.el.node().getBoundingClientRect().height
      }
      this.svg
        .attr("width", this.viewer.width)
        .attr("height", this.viewer.height)
    });
  }
  create(data) {
    if (!data) {
      return false
    }
    if (this.svg) {
      this.el.html('')
    }
    this.svg = this.el.append("svg")
      .attr("width", this.viewer.width)
      .attr("height", this.viewer.height)
      .call(this.zoomListener)
    this.svg.append('g').append("rect")
      .attr("width", this.viewer.width)
      .attr("height", this.viewer.height)
      .attr("fill", "none")
      .attr("pointer-events", "all")
    this.svgGroup = this.svg.append("g")
    this.data = data
    // Define the root
    this.data.x0 = this.viewer.height / 2
    this.data.y0 = 0
    // Layout the tree initially and center on the root node.
    this.update(this.data)
    this.centerNode(this.data, { y: 120 })
  }
  // Define the zoom function for the zoomable tree
  zoom() {
    this.svgGroup.attr("transform", "translate(" + d3.event.translate + ")scale(" + d3.event.scale + ")");
  }
  // pan
  pan(domNode, direction) {
    let speed = this.panSpeed
    let panTimer = null,
        translateX, translateY
    if (panTimer) {
      clearTimeout(panTimer)
      translateCoords = d3.transform(this.svgGroup.attr("transform"))
      if (direction == 'left' || direction == 'right') {
        translateX = direction == 'left' ?
          translateCoords.translate[0] + speed :
          translateCoords.translate[0] - speed
        translateY = translateCoords.translate[1]
      } else if (direction == 'up' || direction == 'down') {
        translateX = translateCoords.translate[0]
        translateY = direction == 'up' ?
          translateCoords.translate[1] + speed :
          translateCoords.translate[1] - speed
      }
      let scaleX = translateCoords.scale[0]
      let scaleY = translateCoords.scale[1]
      let scale = zoomListener.scale()
      this.svgGroup.transition()
        .attr("transform", "translate(" + translateX + "," + translateY + ")scale(" + scale + ")")
      d3.select(domNode).select('g.node')
        .attr("transform", "translate(" + translateX + "," + translateY + ")")
      this.zoomListener.scale(this.zoomListener.scale())
      this.zoomListener.translate([translateX, translateY])
      panTimer = setTimeout(() => {
        this.pan(domNode, speed, direction)
      }, 50)
    }
  }
  // Helper functions for collapsing and expanding nodes.
  collapse(d) {
    if (d.children) {
      d._children = d.children
      d._children.forEach(collapse)
      d.children = null
    }
  }
  expand(d) {
    if (d._children) {
      d.children = d._children
      d.children.forEach(expand)
      d._children = null
    }
  }
  // Function to update the temporary connector indicating dragging affiliation
  updateTempConnector() {
    var data = [];
    if (this.selectedNode !== null) {
      data = [{
        source: {
          x: this.selectedNode.x0,
          y: this.selectedNode.y0
        },
        target: {
          x: this.draggingNode.x0,
          y: this.draggingNode.y0
        }
      }];
    }
    const link = this.svgGroup.selectAll(".templink").data(data)
    link.enter().append("path")
        .attr("class", "templink")
        .attr("d", d3.svg.diagonal())
        .attr('pointer-events', 'none')
    link.attr("d", d3.svg.diagonal())
    link.exit().remove()
  }
  overCircle(d) {
    this.selectedNode = d
    this.updateTempConnector()
  }
  outCircle(d) {
    this.selectedNode = null
    this.updateTempConnector()
  }
  // Function to center node when clicked/dropped so node doesn't get lost when collapsing/moving with large amount of children.
  centerNode(source, fitTo) {
    let scale = this.zoomListener.scale()
    let x = -source.x0
    let y = -source.y0
    if (fitTo && 'x' in fitTo) {
      x = x * scale + fitTo.x
    } else {
      x = x * scale + this.viewer.width / 2
    }
    if (fitTo && 'y' in fitTo) {
      y = y * scale + fitTo.y
    } else {
      y = y * scale + this.viewer.height / 2
    }
    this.svgGroup.transition()
        .duration(this.duration)
        .attr("transform", "translate(" + x + "," + y + ")scale(" + scale + ")")
    this.zoomListener.scale(scale)
    this.zoomListener.translate([x, y])
  }
  // Toggle children function
  toggleChildren(d) {
    if (d.children) {
      d._children = d.children
      d.children = null
    } else if (d._children) {
      d.children = d._children
      d._children = null
    }
    return d
  }
  // Toggle children on click.
  click(d) {
    if (d3.event.defaultPrevented) {
      return // click suppressed
    }
    d = this.toggleChildren(d)
    this.update(d)
    this.centerNode(d)
  }
  update(source) {
    // Compute the new width, function counts total children of root node and sets tree width accordingly.
    // This prevents the layout looking squashed when new nodes are made visible or looking sparse when nodes are removed
    // This makes the layout more consistent.
    let levelHeight = [1]
    let childCount = (level, n) => {
      if (n.children && n.children.length > 0) {
        if (levelHeight.length <= level + 1) {
          levelHeight.push(0)
        }
        levelHeight[level + 1] += n.children.length
        n.children.forEach(d => {
          childCount(level + 1, d)
        })
      }
    }
    childCount(0, this.data)
    let newWidth = d3.max(levelHeight) * 25// 25 pixels per line
    this.tree = this.tree.size([this.viewer.height, newWidth])
    // Compute the new tree layout.
    let nodes = this.tree.nodes(this.data).reverse()
    let links = this.tree.links(nodes)
    // Set height between levels based on maxLabelLength.
    nodes.forEach(d => {
      d.y = d.depth * this.padding.y
      d.x = d.x * this.padding.x
    })
    // Update the nodes
    let node = this.svgGroup.selectAll("g.node")
      .data(nodes, d => d.id)
    // Enter any new nodes at the parent's previous position.
    let nodeEnter = node.enter().append("g")
      .attr("class", "node")
      .attr("transform", d => "translate(" + source.x0 + "," + source.y0 + ")")
    nodeEnter.append("circle")
      .attr('class', 'nodeCircle')
      .attr("r", 0)
      .style("fill", d => d._children ? "lightsteelblue" : "#fff")
      .on('click', d => this.click(d))
    const fo = nodeEnter.append('foreignObject')
      .attr({
        width: 100,
        height: 25,
        x: -50,
        y: d => d.children || d._children ? -25 : 0
      })
      .attr("class", "fo")
    fo.append('xhtml:div')
      .append('div')
        .html(d => {
          return this.labelContent(d)
        })
        .attr('id', d => d.id)
        .attr('class', d => d.children || d._children ? 'label anchor-bottom' : 'label anchor-top')
        .style("opacity", 0)
    // Change the circle fill depending on whether it has children and is collapsed
    node.select("circle.nodeCircle")
      .attr("r", 7)
      .style("fill", d => d._children ? "lightsteelblue" : "#fff")
    fo.attr({
      height: d => {
        const label = node.select('.label').filter(l => l.id === d.id)
        if (label[0] && label[0][0]) {
          return label[0][0].getBoundingClientRect().height
        }
      },
      y: d => {
        const label = node.select('.label').filter(l => l.id === d.id)
        if (label[0] && label[0][0]) {
          return d.children || d._children ? -label[0][0].getBoundingClientRect().height : 0
        }
      }
    })
    // Transition nodes to their new position.
    let nodeUpdate = node.transition()
      .duration(this.duration)
      .attr("transform", d => "translate(" + d.x + "," + d.y + ")")
    // Fade the text in
    nodeUpdate.select(".label")
      .style("opacity", 1)
    // Transition exiting nodes to the parent's new position.
    let nodeExit = node.exit().transition()
      .duration(this.duration)
      .attr("transform", d => "translate(" + source.x + "," + source.y + ")")
      .remove()
    nodeExit.select("circle")
      .attr("r", 0)
    nodeExit.select(".label")
      .style("opacity", 0)
    // Update the links
    var link = this.svgGroup.selectAll("path.link")
      .data(links, d => d.target.id)
    // Enter any new links at the parent's previous position.
    link.enter().insert("path", "g")
      .attr("class", "link")
      .style("stroke-dasharray", d => d.source.id === 0 || d.target.id === 0 ? '1.5,3' : '5,0')
      .attr("d", d => {
        let o = {
          x: source.x0,
          y: source.y0
        }
        return this.diagonal({
          source: o,
          target: o
        })
      })
    // Transition links to their new position.
    link.transition()
      .duration(this.duration)
      .attr("d", this.diagonal)
    // Transition exiting nodes to the parent's new position.
    link.exit().transition()
      .duration(this.duration)
      .attr("d", d => {
        let o = {
          x: source.x,
          y: source.y
        }
        return this.diagonal({
          source: o,
          target: o
        })
      })
      .remove()

    // Stash the old positions for transition.
    nodes.forEach(d => {
      d.x0 = d.x
      d.y0 = d.y
    })
  }
  // label content
  labelContent(d) {
    const rows = [d].concat(d.partner || [])
    return rows.map((item, i) => {
      const name = (i === 0 && d.id !== 0) ? `<b>${this.nameFormat(item)}</b>` :
        `${this.nameFormat(item)}`
      return d.id !== 0 ?
        `<a href="#"
          class=""
          data-toggle="modal"
          data-target="#rowEdit"
          data-row="${item.id}">${name}</a>` : `${name}`
    }).join('<br>')
  }


}
