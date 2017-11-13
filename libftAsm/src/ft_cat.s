%define READ 3
%define WRITE 4
%define STDOUT 1
%define SYSCALL(nb) 0x2000000 | nb

section .data
   buff: times 1024 db 0
   buffsize equ $ - buff

section .text
global _ft_cat
_ft_cat:
	push rbp
	mov rbp, rsp
	jmp read

impress:
	mov rdi, 1
	mov rdx, rax
	mov rax, SYSCALL(WRITE)
	syscall
	jc err
	pop rdi

read:
	mov rax, SYSCALL(READ)
	push rdi
	lea rsi, [rel buff];point to buffer 2nd arg
	mov rdx, buffsize
	syscall
	jc err
	cmp rax, 0
	jle return
	jmp impress

err:
	pop rdi
	mov rax, 1

return:
	mov rsp, rbp
	pop rbp
	ret