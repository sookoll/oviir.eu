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
    this.x = d3.scale.linear().domain([0, this.viewer.width]).range([0, this.viewer.width]),
    this.y = d3.scale.linear().domain([0, this.viewer.height]).range([0, this.viewer.height]),
    this.zoomListener = d3.behavior.zoom().x(this.x).y(this.y).scaleExtent([1, 16]).on('zoom', () => {
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
        width: this.el.node().getBoundingClientRect().width,
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
      .attr("viewBox", `0 0 ${this.viewer.width} ${this.viewer.height}`)
    this.svgGroup = this.svg.append("g")
    this.svgGroup.append("rect")
      .attr("width", this.viewer.width)
      .attr("height", this.viewer.height)
      .attr("fill", "none")
      .attr("pointer-events", "all")
    this.data = data
    // Define the root
    this.data.x0 = this.viewer.width / 2
    this.data.y0 = this.viewer.height / 2
    // Layout the tree initially and center on the root node.
    this.update(this.data)
    this.svgGroup.call(this.zoomListener)

    //this.centerNode(this.data, { y: 120 })
    this.centerNode(this.data)
  }
  // Function to center node when clicked/dropped so node doesn't get lost when collapsing/moving with large amount of children.
  centerNode(source, fitTo) {
    let scale = this.zoomListener.scale()
    let x = -this.x(source.x0)
    let y = -this.y(source.y0)
    if (fitTo && 'x' in fitTo) {
      x = this.x(x * scale + fitTo.x)
    } else {
      x = this.x(x * scale + this.viewer.width / 2)
    }
    if (fitTo && 'y' in fitTo) {
      y = this.y(y * scale + fitTo.y)
    } else {
      y = this.y(y * scale + this.viewer.height / 2)
    }
    /*this.svgGroup.transition()
        .duration(this.duration)
        .attr("transform", "translate(" + x + "," + y + ")scale(" + scale + ")")*/

    this.zoomListener
      .scale(scale)
      .translate([x, y])
      

    console.log(this.zoomListener)

    /*var nodes = this.svgGroup.selectAll(".node");
    nodes.attr("transform", d => this.transform(d));
    var link = this.svgGroup.selectAll(".link");
    link.attr("d", d => this.translate(d));*/
  }
  // Define the zoom function for the zoomable tree
  zoom() {
    var nodes = this.svgGroup.selectAll(".node");
    nodes.attr("transform", d => this.transform(d));
    var link = this.svgGroup.selectAll(".link");
    link.attr("d", d => this.translate(d));
  }
  transform(d) {
    return "translate(" + this.x(d.x) + "," + this.y(d.y) + ")";
  }
  translate(d) {
    var sourceX = this.x(d.target.parent.x);
    var sourceY = this.y(d.target.parent.y);
    var targetX = this.x(d.target.x);
    var targetY = this.y(d.target.y);
    return `M ${sourceX} ${sourceY}
      C ${sourceX} ${(sourceY + targetY) / 2},
        ${targetX} ${(sourceY + targetY) / 2},
        ${targetX} ${targetY}`;
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
      .style("stroke", d => {
        if(d.target.class === "found") {
          return "#ff4136";
        }
      })
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
  search(id) {
    var paths = this.searchTree(this.data, id, [])
    if (typeof(paths) !== "undefined") {
      this.openPaths(paths)
      this.centerNode(paths[paths.length - 1])
    }
  }
  searchTree(obj,search,path) {
    obj.class = null
    if (obj.id === search) { //if search is found return, add the object to the path and return it
      path.push(obj);
      return path;
    } else if (obj.children || obj._children) { //if children are collapsed d3 object will have them instantiated as _children
      var children = (obj.children) ? obj.children : obj._children;
      for (var i = 0; i < children.length; i++) {
        obj.class = null
        path.push(obj);// we assume this path is the right one
        var found = this.searchTree(children[i], search, path);
        if (found) {// we were right, this should return the bubbled-up path from the first if statement
          return found;
        } else{//we were wrong, remove this parent from the path and continue iterating
          path.pop();
        }
      }
    } else{//not the right object, return false so it will continue to iterate in the loop
      return false;
    }
  }
  openPaths(paths) {
    for (var i = 0; i < paths.length; i++) {
      if (paths[i].id !== 0) {//i.e. not root
        paths[i].class = 'found';
        if (paths[i]._children) { //if children are hidden: open them, otherwise: don't do anything
          paths[i].children = paths[i]._children;
          paths[i]._children = null;
        }
        this.update(paths[i]);
      }
    }
  }

}
