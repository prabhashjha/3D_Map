<!DOCTYPE HTML>
<html>
    <head>
        <style>
            body {
                margin: 0px;
                padding: 0px;
            }
        </style>
    </head>
    <body>
        <div id="container"></div>
        <script type="text/javascript" src="js/three.min.js"></script>
        <script type="text/javascript" src="js/UVsUtils.js"></script>
        <script type="text/javascript" src="js/d3.v2.js"></script>
        <script type="text/javascript" src="js/jquery-1.7.2.js"></script>
        <script defer="defer">
            //VARIABLES          
            var color = ["#5DBCD2","#03FC0A","#004900","#FFFF00","#FD8800","#FD0200","#DE0704","#C30000","#FF00FF","#A460CF"];
            var big_number = 9999999999999999;
            var min_gdp=big_number,max_gdp=0,min_population=big_number,max_population=0,min_unemp=big_number,max_unemp=0,min_debt=big_number,max_debt=0,min_debt=big_number,max_debt=0,min_Unemployment=big_number,max_Unemployment=0; 
            var population,gdp,debt,unemployment;
            
        
        
            //var WIDTH = 1200, HEIGHT = 800; 
            var WIDTH=window.innerWidth , HEIGHT=    window.innerHeight;
            var scene = new THREE.Scene();
            // renderer
            var renderer = new THREE.WebGLRenderer();
            renderer.setSize(WIDTH, HEIGHT);
            document.body.appendChild(renderer.domElement);
            var camera = new THREE.PerspectiveCamera(45,WIDTH / HEIGHT, 1, 10000);
            camera.position.z = 100;
            camera.position.y = -100;
            // camera.position.x = -50;
            camera.lookAt( scene.position );            
     
            /** Custom UV mapper **/
            var uvGenerator = new THREE.UVsUtils.CylinderUVGenerator();
            var directionalLight = new THREE.DirectionalLight(0xffffff);
            directionalLight.position.set(0, -1, 1).normalize();
            scene.add(directionalLight);
            
            // add a base plane on which we'll render our map
            //THREE.PlaneGeometry(width, height, segmentsWidth, segmentsHeight)
            var planeGeo = new THREE.PlaneGeometry(WIDTH, HEIGHT, 10, 10);
            var planeMat = new THREE.MeshLambertMaterial({color: 0x666699});
            var plane = new THREE.Mesh(planeGeo, planeMat);                       
            scene.add(plane);
            
            d3.csv("data/state_gdp_2012.csv", function(state_population) { 
                d3.csv("data/state_latlon.csv", function(state_latlon) {
                    jQuery.getJSON('data/custom_us-states.json', function(data) {
                    
                        for(var x=0;x<state_population.length;x++){
                            if(parseFloat(state_population[x].Population_million)>max_population) max_population=state_population[x].Population_million;
                            if(parseFloat(state_population[x].Population_million)<min_population) min_population=state_population[x].Population_million;
                            if(parseFloat(state_population[x].GDP)>max_gdp) max_gdp=state_population[x].GDP;
                            if(parseFloat(state_population[x].GDP)<min_gdp) min_gdp=state_population[x].GDP;
                            
                            if(parseFloat(state_population[x].Unemployment)>max_Unemployment) max_Unemployment=state_population[x].Unemployment;
                            if(parseFloat(state_population[x].Unemployment)<min_Unemployment) min_Unemployment=state_population[x].Unemployment;
                            
                            if(parseFloat(state_population[x].debt)>max_debt) max_debt=state_population[x].debt;
                            if(parseFloat(state_population[x].debt)<min_debt) min_debt=state_population[x].debt;
                        }
                    
                    
                    
                        for (var i = 0 ; i < data.features.length ; i++) {
                            var geoFeature = data.features[i];
                            if(geoFeature.id!="AK"&&geoFeature.id!="HI"){
                                var tmp,geofeature,latlon_scaled=new Array(), minx=big_number,miny=big_number,displacement=0.5;    
                            
                                jQuery.map(state_population, function(obj) {
      
                                    if(obj.abbr.toUpperCase() === geoFeature.id.toUpperCase()) 
                                    {      
                            
                                        population =  parseInt(get_scaled_value(min_population,max_population,1,8,obj.Population_million));
                                        gdp =  parseInt(get_scaled_value(min_gdp,max_gdp,1,8,obj.GDP));

                                        unemployment =  parseInt(get_scaled_value(min_Unemployment,max_Unemployment,1,8,obj.Unemployment)+0.5);
                                        debt =  parseInt(get_scaled_value(min_debt,max_debt,1,8,obj.debt)+0.5);

                                    }                       
                        
                                }); 
                            
                                var latlon = jQuery.map(state_latlon, function(obj) {
                                    if(obj.state.toUpperCase() === geoFeature.id.toUpperCase()) 
                                    {
                                        return [obj.longitude,obj.latitude];
                                    }
                                
                                });
                                  
                                latlon_scaled[0]=  get_scaled_value(-130,-60,-70,70,latlon[0]); //latitute
                                latlon_scaled[1]=  get_scaled_value(20,50,-40,40,latlon[1]);//longitude
                            
                                var extrude =getRandomInt (3, 8);
                                 
                                for(var loop_mesh=0;loop_mesh<geoFeature.geometry.coordinates.length;loop_mesh++){
                                    var state_shape = new THREE.Shape();
                                    //checking how many mesh each state has
                                    if(geoFeature.geometry.coordinates[loop_mesh].length==1) 
                                        geofeature = geoFeature.geometry.coordinates[loop_mesh][0];
                                    else 
                                        geofeature = geoFeature.geometry.coordinates[loop_mesh];

                                    for(var cor=0;cor<geofeature.length;cor++){
                                        tmp = geofeature[cor];
                                        tmp[0]=  get_scaled_value(-130,-60,-70,70,tmp[0])+ displacement*latlon_scaled[0]; //latitude
                                        tmp[1]=  get_scaled_value(20,50,-40,40,tmp[1])+displacement*latlon_scaled[1]; //longitude
                                        // console.log(tmp[0]+"        "+tmp[1]);
                                        var method = cor ? "lineTo":"moveTo";
                                        // console.log(cor+"-"+method+" "+tmp[0]+","+tmp[1]);
                                        if(minx>tmp[0]) minx=tmp[0];
                                        if(miny>tmp[1]) miny=tmp[1];
                                        state_shape[method](tmp[0], tmp[1]);
                               
                                    }
                                    var img="img/"+unemployment+".png";
                                    var tex = setupTexture_from_img(img,population);
                                    var exoption = {
                                        bevelEnabled: false,
                                        bevelSize: 1,
                                        amount: extrude,
                                        extrudeMaterial: 0,
                                        material: 1,
                                        uvGenerator: uvGenerator
                                    };
                                    var geom = state_shape.extrude(exoption);
                                    var mesh=setupMesh( geom , tex,population);
                                }
                           
                                latlon_scaled[0]*=(1+displacement);
                                latlon_scaled[1]*=(1+displacement);                                                       
                            }
                        }
                        animate();
            
                    });
                });
            });
            
            
            function setupMesh(geom, side_texture,color_number) {
                var materials = [
                    new THREE.MeshBasicMaterial( { color: parseInt("#000000".replace("#",""), 16)} ),
                    new THREE.MeshBasicMaterial( { map: side_texture//,
                        // opacity: 0.5,
                        //color: parseInt(color[color_number].toString().replace("#",""), 16)
             
                    } )
                ];
                // var material =  new THREE.MeshBasicMaterial( { map: side_texture } );
                var mesh = new THREE.Mesh(geom, new THREE.MeshFaceMaterial(materials));

                scene.add(mesh);
                return mesh;
            }
            function setupTexture_from_img(dat,zoom) {
                var t =  THREE.ImageUtils.loadTexture(dat);
                t.wrapS = THREE.RepeatWrapping; //vertical stacking
                t.wrapT = THREE.RepeatWrapping; //horizontal stacking
                t.repeat.set(0.1,0.1);
                t.offset.set(1,1);
                t.needsUpdate=true;
                return t;
            }
            function get_scaled_value(min,max,a,b,value){
                var tmp = (b-a)*(value-min)/(max-min) +a;
                if(isNaN(parseInt(tmp))) return a; else return tmp;//parseInt(tmp);
            }
            function animate(){
                renderer.render(scene, camera);
                requestAnimationFrame(function(){
                    animate();
                });
            }
            function getRandomInt (min, max) {
                return Math.floor(Math.random() * (max - min + 1)) + min;
            } 
        </script>
    </body>
</html>