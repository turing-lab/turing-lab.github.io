---
layout: post
title:  "Galileo Hand"
date:   2016-11-30 17:00:00
category: project
download: false
---

# Galileo Healthcare Monitor System
***

The specialized medical equipment required to diagnose, treat and monitor specific types of diseases is very expensive and somewhat unreachable for poor communities with limited resources that have no access to technology. New approaches have to be implemented in order to ensure health-care delivery and medical assistance to rural communities that lack access to proper instrumentation, particularly when specialized medical teams travel through those communities performing medical campaigns. A mobile based medical instrumentation kit implemented on Cypress’s PSoC Analog Coprocessor (Programmable analog front ends and a signal processing engine based on ARM Cortex-M3 processor with serial communication capabilities, i.e. SPI, I2C, UART) is proposed in order to perform signal conditioning and to integrate specific medical instrumentation such as electrocardiogram (ECG with 3 derived and 12 leads), pulse oximeter, glucose meter, hermometer and blood pressure monitor. This kit is intended to allow medical staff to monitor biological parameters in order to improve monitoring and control of common diseases through a transparent communication between medical instrumentation kits and smartphones implemented with a Smartphone Ad-Hoc Networks (SPANs), taking advantage of bluetooth low energy (BLE) modules to create a link between multiple devices, leveraging its capabilities to implement an user-friendly interface and allowing cloud based data storage capabilities that can be accessed and shared between medical specialist to improve treatment and monitoring of each individual patient.

![SystemArchitecture](/misc/img/projects/medical/SA.png)
> System Architecture of Smartphone Based Healthcare Monitor System.

The User Interface (UI), implemented on an Android application for smartphones and tablets, enables the doctor and the patient to cost effectively monitor and analyze different measurements from a group of predefined biological parameters according to the necessities of the patient, e.g. a diabetic patient could benefit from a glucometer, a blood pressure monitor or a ECG (in case of previous history of cardiovascular disease or arrhythmias) but a patient with Chronic Obstructive Pulmonary Disease (COPD) could benefit more from a pulse oximeter and temperature control for exacerbations. Moreover, the application supports running in tablets that usually have larger screens, which can display more information and give a better user experience. The main aim is that this system can be personalized to fit individual needs, trusting that people with chronic conditions, such as previous described, can have a better control of their diseases and that this can result at long term in a reduction of comorbidities. The latter would be investigated after developing this project and letting the possibility of implementing wearable based on body sensor networks (BSN), in order to collect realtime data that can be remotely monitored

![BlockDiagram](/misc/img/projects/medical/Board.png)
> Custom Sensor Board Block Diagram.


