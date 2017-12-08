section .text
global _decrypt_true

;_start:
_decrypt_true:
	push rbp
	mov rbp, rsp
	;RDI, RSI = rsi, RDX = rdx, RCX, R8, R9
	;mov rdi, 0x22222222222 ; data[k]
	;mov rsi, 0x33333333333 ; index
	;mov rdx, 0x44444444444 ; size
	;mov rcx, 0x55555555555 ; k
	;mov rbx, rcx		   ; k_start
	;mov r8, 0x66666666666 ; key
	;revoir la division
	
	mov rbx, rcx		   ; k_start
	mov rdx, 1 ; sign

loop:
	cmp rcx, rdx ;si arrive fin de .text
	je return
	;compare with mask
	
	push rbx ;remettre
	push rdx ;remettre
	
	mov rbx, rdi 	;rbx = data[k] --> x 
	mov rdx, rdi 	;rdx = data[k] --> y

	cmp rdx, 0
	jg more
	
	push rdx
	and rbx, rsi 	;x = data[k] & index
	push rsi		; on sauvegarde l'index
	push rbx
	mov rbx, 2
	div rbx 		;rbx = index-- (on se deplace d'un bit)
	and rdx, rbx	;rbx(y) = data[k] & index--
	pop rbx
	pop rsi	
	sub rdi, rbx	;data[k] - x
	sub rdi, rdx	;data[k] - y
	shr rbx, 1		;x >> 
	sal rdx, 1		;y <<
	add rdi, rbx	;data[k] + x
	add rdi, rdx	;data[k] + y
	pop rdx
	and rdx, rsi
	pop rdx
	pop rbx
	inc rcx ;increment de k fin de loop
	mov rdi, [rdi + rcx] ;increment de rdi

index:
	push rcx
	sub rcx, rbx
	push rdx ; on stocke le signe
	;push rsi ; stocke l'index
	push rax
	push rcx
	mov rax, rcx
	mov rcx, r8
	mov rdx, 0
``````````````````````````````````````;revoir div
	div rcx
	mov rdx, rcx

	pop rcx
	pop rax
	cmp rdx, 0
	je key_index
	;pop rsi
	pop rdx

indexi:
	cmp rdx, 0
	ja pos
	cmp rsi, 2
	jg moins
	mov rdx, 1
	jmp loop

key_index:
``````````````````````````````````````;revoir div
	;pop rdx
	mov rsi, r8
	div rsi, 7
	mov rsi, rdx
	pop rdx
	jmp loop

more:
	and rbx, rsi 	;x = data[k] & index
	imul rbx, 2 		;rbx = index++ (on se deplace d'un bit)
	and rdx, rbx	;rbx(y) = data[k] & index++
	sub rdi, rbx	;data[k] - x
	sub rdi, rdx	;data[k] - y
	sal rbx, 1			;x << 
	shr rdx, 1			;y >>
	add rdi, rbx	;data[k] + x
	add rdi, rdx	;data[k] + y
	pop rdx
	pop rbx
	inc rcx ;increment de k fin de loop
	mov rdi, [rdi + rcx] ;increment de rdi
	jmp index

plus:
	imul rsi, 2

moins:
;a revoir
	div rsi, 2

pos:
	cmp rsi, 64
	jl plus
	mov rdx, -1
	jmp loop

return:
	mov rsi,1                     ; [1] - sys_write
	mov rdi,1                     ; 0 = stdin / 1 = stdout / 2 = stderr
	lea rdx,[rel msg]             ; pointer(mem address) to msg (*char[])
	mov rdx, msg_end - msg        ; msg size
	syscall                       ; calls the function stored in rsi
	pop rbp
	ret
	;; jump to e_entry
	;mov rsi, 0x1111111111111111   ; address changed during injection
	;jmp rsi

align 8
	msg     db "....WOODY....", 10
	msg_end db 0x0
