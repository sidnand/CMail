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




INSERT INTO SupportTeam VALUES (1, 'Canada', 'Toronto');
INSERT INTO SupportTeam VALUES (2, 'Canada', 'Vancouver');
INSERT INTO SupportTeam VALUES (3, 'Canada', 'Montreal');
INSERT INTO SupportTeam VALUES (4, 'United States', 'New York');
INSERT INTO SupportTeam VALUES (5, 'United States', 'Los Angeles');

INSERT INTO "User" VALUES (7786652266, 'Siddharth', 'Nand');
INSERT INTO "User" VALUES (4432348866, 'Michael', 'Perkins');
INSERT INTO "User" VALUES (3441128899, 'Saif', 'Karnawi');
INSERT INTO "User" VALUES (5557771111, 'Jennifer', 'Smith');
INSERT INTO "User" VALUES (6668882222, 'Lora', 'Hill');

INSERT INTO Admin VALUES (1010, 'Dave', 'Colby', 1);
INSERT INTO Admin VALUES (1011, 'Emma', 'Stome', 1);
INSERT INTO Admin VALUES (1012, 'Jessica', 'Khali', 2);
INSERT INTO Admin VALUES (1013, 'Jone', 'Doe', 3);
INSERT INTO Admin VALUES (1014, 'Jane', 'Doe', 4);

INSERT INTO Account VALUES ('siddharthnand', TO_DATE('2019-01-01', 'YYYY-MM-DD'), 7786652266, 1010);
INSERT INTO Account VALUES ('michaelperkins', TO_DATE('2019-01-01', 'YYYY-MM-DD'), 4432348866, 1011);
INSERT INTO Account VALUES ('saifkarnawi', TO_DATE('2019-01-01', 'YYYY-MM-DD'), 3441128899, 1012);
INSERT INTO Account VALUES ('jennifersmith', TO_DATE('2019-01-01', 'YYYY-MM-DD'), 5557771111, 1013);
INSERT INTO Account VALUES ('lorahill', TO_DATE('2019-01-01', 'YYYY-MM-DD'), 6668882222, 1014);
INSERT INTO Account VALUES ('k_smith', TO_DATE('2019-01-01', 'YYYY-MM-DD'), 6668882222, 1014);

INSERT INTO Mailbox VALUES (1, 'siddharthnand');
INSERT INTO Mailbox VALUES (2, 'michaelperkins');
INSERT INTO Mailbox VALUES (3, 'saifkarnawi');
INSERT INTO Mailbox VALUES (4, 'jennifersmith');
INSERT INTO Mailbox VALUES (5, 'lorahill');
INSERT INTO Mailbox VALUES (6, 'k_smith');

INSERT INTO CustomMailbox VALUES (7, 'siddharthnand', TO_DATE('2019-01-01', 'YYYY-MM-DD'), 'Important');
INSERT INTO CustomMailbox VALUES (8, 'michaelperkins', TO_DATE('2019-01-01', 'YYYY-MM-DD'), 'Work');
INSERT INTO CustomMailbox VALUES (9, 'saifkarnawi', TO_DATE('2019-01-01', 'YYYY-MM-DD'), 'Family');
INSERT INTO CustomMailbox VALUES (10, 'jennifersmith', TO_DATE('2019-01-01', 'YYYY-MM-DD'), 'Friends');
INSERT INTO CustomMailbox VALUES (11, 'lorahill', TO_DATE('2019-01-01', 'YYYY-MM-DD'), 'Important');
INSERT INTO CustomMailbox VALUES (12, 'k_smith', TO_DATE('2019-01-01', 'YYYY-MM-DD'), 'Junk');

INSERT INTO Email VALUES (email_sequence.nextval, 'siddharthnand', 'michaelperkins', 'Hello, how are you?', 2);
INSERT INTO Email VALUES (email_sequence.nextval, 'michaelperkins', 'siddharthnand', 'I am good, how are you?', 1);
INSERT INTO Email VALUES (email_sequence.nextval, 'saifkarnawi', 'siddharthnand', 'Can we have a meeting tomorrow at 5pm?', 1);
INSERT INTO Email VALUES (email_sequence.nextval, 'saifkarnawi', 'michaelperkins', 'Can we delay it till next week?', 2);
INSERT INTO Email VALUES (email_sequence.nextval, 'michaelperkins', 'saifkarnawi', 'I am going to be late for dinner tonight', 3);
INSERT INTO Email VALUES (email_sequence.nextval, 'siddharthnand', 'saifkarnawi', 'Ok, I will wait for you', 3);
INSERT INTO Email VALUES (email_sequence.nextval, 'siddharthnand', 'saifkarnawi', 'You did not get the job offer', 3);
INSERT INTO Email VALUES (email_sequence.nextval, 'saifkarnawi', 'siddharthnand', 'Ok, I will wait for you', 1);
INSERT INTO Email VALUES (email_sequence.nextval, 'siddharthnand', 'michaelperkins', 'Email test message.', 2);
INSERT INTO Email VALUES (email_sequence.nextval, 'siddharthnand', 'saifkarnawi', 'Your late for work!', 3);
INSERT INTO Email VALUES (email_sequence.nextval, 'siddharthnand', 'saifkarnawi', 'Nice to meet you', 3);
INSERT INTO Email VALUES (email_sequence.nextval, 'siddharthnand', 'michaelperkins', 'Email test message 2', 2);
INSERT INTO Email VALUES (email_sequence.nextval, 'siddharthnand', 'michaelperkins', 'Congrats on your job offer!', 2);
INSERT INTO Email VALUES (email_sequence.nextval, 'saifkarnawi', 'siddharthnand', 'Your late again', 1);
INSERT INTO Email VALUES (email_sequence.nextval, 'saifkarnawi', 'siddharthnand', 'Where are the keys!?', 1);

INSERT INTO Contact VALUES ('siddharthnand@gmail.com', 'Siddharth', 'Nand');
INSERT INTO Contact VALUES ('michaelperkins@gmail.com', 'Michael', 'Perkins');
INSERT INTO Contact VALUES ('saifkarnawi@mail.com', 'Saif', 'Karnawi');
INSERT INTO Contact VALUES ('nand_s@yahoo.com', 'Siddharth', 'Nand');
INSERT INTO Contact VALUES ('jennifersmith@mail.com', 'Jennifer', 'Smith');
INSERT INTO Contact VALUES ('admin@ubc.ca', 'UBC', 'Admin');

INSERT INTO Attachment VALUES ('attachment1', 1, 'pdf');
INSERT INTO Attachment VALUES ('attachment2', 2, 'doc');
INSERT INTO Attachment VALUES ('attachment3', 3, 'html');
INSERT INTO Attachment VALUES ('attachment4', 4, 'zip');
INSERT INTO Attachment VALUES ('attachment5', 5, 'pdf');

INSERT INTO UserContacts VALUES (userContact_sequence.nextval, 7786652266, 'michaelperkins@mail.com');
INSERT INTO UserContacts VALUES (userContact_sequence.nextval, 7786652266, 'saifkarnawi@mail.com');
INSERT INTO UserContacts VALUES (userContact_sequence.nextval, 7786652266, 'nand_s@gmail.com');
INSERT INTO UserContacts VALUES (userContact_sequence.nextval, 7786652266, 'admin@ubc.ca');
INSERT INTO UserContacts VALUES (userContact_sequence.nextval, 7786652266, 'jennifersmith@mail.com');
INSERT INTO UserContacts VALUES (userContact_sequence.nextval, 4432348866, 'michaelperkins@mail.com');
INSERT INTO UserContacts VALUES (userContact_sequence.nextval, 4432348866, 'saifkarnawi@mail.com');
INSERT INTO UserContacts VALUES (userContact_sequence.nextval, 4432348866, 'nand_s@gmail.com');
INSERT INTO UserContacts VALUES (userContact_sequence.nextval, 4432348866, 'admin@ubc.ca');
INSERT INTO UserContacts VALUES (userContact_sequence.nextval, 3441128899, 'jennifersmith@mail.com');
INSERT INTO UserContacts VALUES (userContact_sequence.nextval, 3441128899, 'michaelperkins@mail.com');
INSERT INTO UserContacts VALUES (userContact_sequence.nextval, 3441128899, 'saifkarnawi@mail.com');
INSERT INTO UserContacts VALUES (userContact_sequence.nextval, 3441128899, 'nand_s@gmail.com');
INSERT INTO UserContacts VALUES (userContact_sequence.nextval, 3441128899, 'admin@ubc.ca');
INSERT INTO UserContacts VALUES (userContact_sequence.nextval, 3441128899, 'jennifersmith@mail.com');