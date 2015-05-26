
<project name="Java project" default="dist">


     <!--
     
     1. compile the java source
     2. create a jar file
     3. copy the web files into the build folder
     4. create a zip file containing jar as well as web files
     
     -->
<!-- <property name="appversion" value="1.0"/> -->
<property file="build.properties"/>


<target name="clean">
	<delete dir="build/classes"/>
	<delete dir="build/jar"/>
</target>

<target name="init" depends="clean">
	<mkdir dir="build/classes"/>
	<mkdir dir="build/jar"/>
</target>

<target name="compile" depends="init">
	<javac srcdir="src"
		destdir="build/classes"/>
</target>

<target name="jar" depends="compile">
	<jar destfile="build/jar/app-${appversion}.jar" basedir="build/classes"/>
</target>

<target name="copywebfiles">
	<copy todir="build/web">
		<fileset dir="web">
		</fileset>
	</copy>
</target>

<target name="dist" depends="jar,copywebfiles">
	<zip destfile="dist/app-${appversion}.zip">
		<fileset dir="build/web"/>
		<fileset dir="build/jar"/>
	</zip>

</target>
</project>



