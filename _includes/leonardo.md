# Telepresence Robot: Leonardo GreenMoov

***

Leonardo GreenMoov is a telepresence robot, designed to enable you and other people to remote control a humanoid robot while giving you a sense of being on the location the robot is. This is achieved by using sensors in the robot so he can transmit what his senses perceive (vision, audio, other) and various motion sensors to transmit the human operator's movement into the robot's servo motors.

The humanoid robot is based on the open source InMoov ([https://www.inmoov.fr](https://www.inmoov.fr)) Robot. We control the head using an Oculus VR headset, and the hand and arm using a Leap Motion. We are currently experimenting with other types of sensors and hardware to enhance user experience and control.

Telepresence refers to the concept of making a person feel as if they were in another place, and the people in the other place to feel like they are really interacting with the other person. Right now, telepresence is mostly based on vision only, e.g. videoconferencing; if we add the means to control movement, we can get a far better experience.

## How does it work?
The robot uses HD cameras to see the world, this information is transferred through the network and displayed directly into the user's eyes using the Oculus VR headset; The headset captures the user’s head movement, while other sensors capture body movement which is then sent back to the robot and gets translated into servo motor movements in order to replicate what the user is doing.

## Aplications
A telepresence robot could be used to reach places humanity has never reached, such as places with extreme pressure, temperature, radioactivity, or other hostile conditions. It could also be used for
telemedicine, remote surgery, deactivating bombs or dangerous situations such as warfare or hostage situations.

## Future Work
Once a telepresence robot exists, one could deep learn human behavior and use that knowledge to teach robots housekeeping and domestic services tasks, cooking, plumbing and general handyman chores could be delegated to robots; Medical diagnosis, surgery and almost any task done by humans could be automated and done by a humanoid robot.
