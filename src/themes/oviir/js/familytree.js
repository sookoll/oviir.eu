class FamilyTree {
  constructor(options) {
    this.el = d3.select(options.target)
    this.viewer = {
      width: this.el.node().getBoundingClientRect().width,
      height: this.el.node().getBoundingClientRect().height
    }
    this.zoomListener = d3.behavior.zoom().scaleExtent([0.1, 3]).on('zoom', () => {
      this.zoom()
    })
    this.tree = d3.layout.tree()
      .size([this.viewer.height, this.viewer.width])
    this.svg = this.el.append("svg")
      .attr("width", this.viewer.width)
      .attr("height", this.viewer.height)
      .call(this.zoomListener)
    this.diagonal = d3.svg.diagonal()
      .projection(d => [d.x, d.y])
    this.svgGroup = this.svg.append("g")
    this.maxLabelLength = 0
    this.panSpeed = 200
    this.selectedNode = null
    this.duration = 750
    this.data = []
  }
  load(data) {
    if (!data.length) {
      return false
    }
    let totalNodes = 0
    this.data = data

    // Call visit function to establish maxLabelLength
    this.visit(this.data[0], d => {
      totalNodes++
      this.maxLabelLength = Math.max(
        d.firstname.length + d.lastname.length,
        this.maxLabelLength
      )
    }, d => {
      return d.children && d.children.length > 0 ? d.children : null
    })

    // Define the root
    let root = this.data[0]
    root.x0 = this.viewer.height / 2
    root.y0 = 0

    // Layout the tree initially and center on the root node.
    this.update(root)
    this.centerNode(root)
  }
  reload(data) {
    this.load(data)
  }
  // A recursive helper function for performing some setup by walking through all nodes
  visit(parent, visitFn, childrenFn) {
    if (!parent) {
      return
    }
    visitFn(parent)
    let children = childrenFn(parent)
    if (children) {
      let count = children.length
      for (let i = 0; i < count; i++) {
        this.visit(children[i], visitFn, childrenFn)
      }
    }
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
  centerNode(source) {
    let scale = this.zoomListener.scale()
    let x = -source.x0
    let y = -source.y0
    x = x * scale + this.viewer.width / 2
    y = y * scale + this.viewer.height / 2
    d3.select('g').transition()
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
    childCount(0, this.data[0])
    let newWidth = d3.max(levelHeight) * 25// 25 pixels per line
    this.tree = this.tree.size([this.viewer.height, newWidth])
    // Compute the new tree layout.
    let nodes = this.tree.nodes(this.data[0]).reverse()
    let links = this.tree.links(nodes)
    // Set height between levels based on maxLabelLength.
    nodes.forEach(d => {
      d.y = d.depth * 150
      d.x = d.x * 3
    })

    // Update the nodes
    let node = this.svgGroup.selectAll("g.node")
      .data(nodes, d => d.id || (d.id = ++i))
    // Enter any new nodes at the parent's previous position.
    let nodeEnter = node.enter().append("g")
      .attr("class", "node")
      .attr("transform", d => "translate(" + source.x0 + "," + source.y0 + ")")
      .on('click', d => this.click(d))
    nodeEnter.append("circle")
      .attr('class', 'nodeCircle')
      .attr("r", 0)
      .style("fill", d => d._children ? "lightsteelblue" : "#fff")
    nodeEnter.append('foreignObject')
      .attr({
        width: 100,
        height: 50,
        x: d => d.children || d._children ? 10 : -50,
        y: d => d.children || d._children ? -50 : 10
      })
      .append('xhtml:div')
        .append('div')
          .html(d => {
            return this.labelContent(d)
          })
          .attr('class', 'label')
    /*nodeEnter.append("text")
      .attr("x", d => d.children || d._children ? -10 : 10)
      .attr("dy", ".35em")
      .attr('class', 'nodeText')
      .attr("text-anchor", d => d.children || d._children ? "end" : "start")
      .html(d => {
        return this.labelContent(d)
      })
      .style("fill-opacity", 0)*/
    // phantom node to give us mouseover in a radius around it
    nodeEnter.append("circle")
      .attr('class', 'ghostCircle')
      .attr("r", 30)
      .attr("opacity", 0.2) // change this to zero to hide the target area
      .style("fill", "red")
      .attr('pointer-events', 'mouseover')
      .on("mouseover", node => {
        this.overCircle(node)
      })
      .on("mouseout", node => {
        this.outCircle(node)
      })
    // Update the text to reflect whether node has children or not.
    /*node.select('text')
      .attr("x", d => d.children || d._children ? -10 : 10)
      .attr("text-anchor", d => d.children || d._children ? "end" : "start")
      .html(d => {
        return this.labelContent(d)
      })*/
    // Change the circle fill depending on whether it has children and is collapsed
    node.select("circle.nodeCircle")
      .attr("r", 4.5)
      .style("fill", d => d._children ? "lightsteelblue" : "#fff")
    // Transition nodes to their new position.
    let nodeUpdate = node.transition()
      .duration(this.duration)
      .attr("transform", d => "translate(" + d.x + "," + d.y + ")")

    // Fade the text in
    nodeUpdate.select("text")
      .style("fill-opacity", 1)

    // Transition exiting nodes to the parent's new position.
    let nodeExit = node.exit().transition()
      .duration(this.duration)
      .attr("transform", d => "translate(" + source.x + "," + source.y + ")")
      .remove()
    nodeExit.select("circle")
      .attr("r", 0)
    nodeExit.select("text")
    .style("fill-opacity", 0)

    // Update the links
    var link = this.svgGroup.selectAll("path.link")
      .data(links, d => d.target.id)
    // Enter any new links at the parent's previous position.
    link.enter().insert("path", "g")
      .attr("class", "link")
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
    const partners = d.partner ?
      d.partner.map(p => `${p.firstname} ${p.lastname}`) : []
    return `<b>${d.firstname} ${d.lastname}</b><br>${partners.join('<br>')}`
  }


}
