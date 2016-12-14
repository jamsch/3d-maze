import io
from shutil import copyfile
file = 'wall.png'
for i in xrange(1,1000):
	copyfile(file, 'textures/{:04d}'.format(i)+'.png')