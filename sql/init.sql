drop table SUPPORTTEAM cascade constraints;
drop table "User" cascade constraints;
drop table ADMIN cascade constraints;
drop table ACCOUNT cascade constraints;
drop table MAILBOX cascade constraints;
drop table CUSTOMMAILBOX cascade constraints;
drop table EMAIL cascade constraints;
drop table ATTACHMENT cascade constraints;
drop table CONTACT cascade constraints;
drop table USERCONTACTS cascade constraints;

DROP SEQUENCE email_sequence;
DROP SEQUENCE userContact_sequence;



CREATE TABLE SupportTeam (

    teamCode NUMBER,
    country VARCHAR(20) NOT NULL,
    city VARCHAR(20) NOT NULL,

    PRIMARY KEY (teamCode)

);

CREATE TABLE "User" (

    phoneNumber NUMBER,
    firstName VARCHAR(20) NOT NULL,
    lastName VARCHAR(20) NOT NULL,

    PRIMARY KEY (phoneNumber)

);

CREATE TABLE Admin (

    agentNumber NUMBER,
    firstName VARCHAR(20) NOT NULL,
    lastName VARCHAR(20) NOT NULL,
    worksFor NUMBER NOT NULL,

    PRIMARY KEY (agentNumber),
    FOREIGN KEY (worksFor) REFERENCES SupportTeam(teamCode) ON DELETE CASCADE

);

CREATE TABLE Account (

    username VARCHAR(20),
    dateCreated DATE NOT NULL,
    ownersPhoneNumber NUMBER NOT NULL,
    adminsNumber NUMBER NOT NULL,

    PRIMARY KEY (username),
    FOREIGN KEY (ownersPhoneNumber) REFERENCES "User"(phoneNumber) ON DELETE CASCADE,
    FOREIGN KEY (adminsNumber) REFERENCES Admin(agentNumber) ON DELETE CASCADE

);

CREATE TABLE Mailbox (

    mailboxID NUMBER,
    ownersUsername VARCHAR(50) NOT NULL,

    PRIMARY KEY (mailboxID),
    FOREIGN KEY (ownersUsername) REFERENCES Account(username) ON DELETE CASCADE

);

CREATE TABLE CustomMailbox (

    mailboxID NUMBER,
    ownersUsername VARCHAR(50) NOT NULL,
    dateCreated DATE NOT NULL,
    customLabel VARCHAR(20) NOT NULL,

    PRIMARY KEY (mailboxID),
    FOREIGN KEY (ownersUsername) REFERENCES Account(username) ON DELETE CASCADE

);

CREATE TABLE Email (

    emailID NUMBER,
    sender VARCHAR(20) NOT NULL,
    recipient VARCHAR(20) NOT NULL,
    body VARCHAR(1000) NOT NULL,
    mailboxID NUMBER NOT NULL,

    PRIMARY KEY (emailID),
    FOREIGN KEY (sender) REFERENCES Account(username) ON DELETE CASCADE,
    FOREIGN KEY (recipient) REFERENCES Account(username) ON DELETE CASCADE,
    FOREIGN KEY (mailboxID) REFERENCES Mailbox(mailboxID) ON DELETE CASCADE

);

CREATE TABLE Attachment (

    fileName VARCHAR(50),
    emailID NUMBER NOT NULL,
    fileType VARCHAR(5) NOT NULL,

    PRIMARY KEY (fileName, emailID),
    FOREIGN KEY (emailID) REFERENCES Email(emailID) ON DELETE CASCADE

);

CREATE TABLE Contact (

    emailAddress VARCHAR(50),
    firstName VARCHAR(20) NOT NULL,
    lastName VARCHAR(20) NOT NULL,

    PRIMARY KEY (emailAddress)

);

CREATE TABLE UserContacts (

    userContactID NUMBER,
    usersPhoneNumber NUMBER NOT NULL,
    contactsEmailAddress VARCHAR(50) NOT NULL,

    PRIMARY KEY (userContactID),
    FOREIGN KEY (usersPhoneNumber) REFERENCES "User"(phoneNumber) ON DELETE CASCADE

);




CREATE SEQUENCE email_sequence
    START WITH 1
    INCREMENT BY 1
    NOCACHE
    NOCYCLE;

CREATE SEQUENCE userContact_sequence
    START WITH 1
    INCREMENT BY 1
    NOCACHE
    NOCYCLE;